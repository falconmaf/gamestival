<?php

namespace Wave\Plugins\Discussions\Components;

use Livewire\Component;
use Filament\Forms\Form;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Concerns\InteractsWithForms;
use Validator;
use Wave\Plugins\Discussions\Models\Models;
use Wave\Plugins\Discussions\Events\NewDiscussionPostCreated;

class Discussion extends Component implements HasForms
{
    use InteractsWithForms;

    public ?array $data = [];
    public ?array $dataReply = [];
    public $title;
    public $slug;
    public $comment;
    public $discussion_slug;
    public $editing = false;
    public $editingTitle;
    public $editingContent;
    public $subscribers;

    protected function getForms(): array
    {
        return [
            'form',
            'replyForm',
        ];
    }

    public $user_subscribed = false;

    public $rules = [
        'comment' => 'required|min:6'
    ];

    public function mount($discussion_slug)
    {
        $this->discussion_slug = $discussion_slug;
        $this->getSubscribers();
        if(!auth()->guest()){
            $this->user_subscribed = $this->discussion->subscribers->contains(auth()->user()->id);
        }

        $this->form->fill();
        $this->replyForm->fill();
    }

    protected function getListeners()
    {
        return [
            'toggleNotification' => 'toggleNotification',
        ];
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                $this->getEditor('content')
                    ->label(false)
                    ->placeholder(trans('discussions::messages.editor.content'))
            ])
            ->statePath('data');
    }

    public function replyForm(Form $form): Form
    {
        return $form
            ->schema([
                $this->getEditor('comment')
                    ->label(false)
                    ->placeholder(trans('discussions::messages.editor.reply'))
            ])
            ->statePath('dataReply');
    }

    private function getEditor(string $name)
    {
        return match(config('discussions.editor')) {
            'textarea' => Textarea::make($name)->rows(8),
            'richeditor' => RichEditor::make($name),
            'markdown' => MarkdownEditor::make($name),
        };
    }

    public function getDiscussionProperty()
    {
        return Models::discussion()->where('slug', $this->discussion_slug)->firstOrFail();
    }

    public function answer()
    {
        $state = $this->replyForm->getState();
        $this->comment = $state['comment'];

        $validator = Validator::make($this->getDataForValidation($this->rules), $this->rules);

        if ($validator->fails()) {

            Notification::make()
                ->title('Validation error')
                ->danger()
                ->body($validator->errors()->first())
                ->send();
            return;
        }

        if ($this->checkTimeBetweenPosts() === false) {
            return;
        }

        $post = Models::post()->create([
            'content' => $this->comment,
            'discussion_id' => $this->discussion->id,
            'user_id' => auth()->user()->id,
        ]);

        event(new NewDiscussionPostCreated($post));

        if ($this->discussion->subscribers->contains(auth()->user()->id) == false) {
            $this->discussion->subscribers()->attach(auth()->user()->id);
        }

        $this->comment = '';

        $this->dispatch('postAdded');

        Notification::make()
                ->title(trans('discussions::alert.success.reason.submitted_to_post'))
                ->success()
                ->send();
        return;
    }

    public function checkTimeBetweenPosts()
    {
        if (config('discussions.security.limit_time_between_posts') === true) {
            $lastPost = Models::post()->where('user_id', auth()->user()->id)->orderBy('created_at', 'desc')->first();
            if ($lastPost != null) {
                $timeBetween =  abs(now()->diffInMinutes($lastPost->created_at));
                if ($timeBetween < config('discussions.security.time_between_posts')) {
                    Notification::make()
                        ->title(trans('discussions::alert.danger.reason.prevent_spam', ['minutes' => config('discussions.security.time_between_posts')]))
                        ->warning()
                        ->send();
                    return false;
                }
            }
        }
    }

    public function deleteDiscussion()
    {
        if (auth()->user()->id != $this->discussion->user_id) {
            Notification::make()
                ->title(trans('discussions::alert.danger.reason.destroy'))
                ->warning()
                ->send();
            return;
        }

        $this->discussion->delete();
        Notification::make()
                ->title(trans('discussions::alert.success.reason.destroy'))
                ->success()
                ->send();

        return redirect()->route('discussions');
    }

    public function editDiscussion()
    {
        if (auth()->user()->id != $this->discussion->user_id) {
            Notification::make()
                ->title(trans('discussions::alert.danger.reason.update_post'))
                ->warning()
                ->send();
            return;
        }
        $this->editing = true;
        $this->editingTitle = $this->discussion->title;
        $this->editingContent = $this->discussion->content;

        $this->form->fill([
            'content' => $this->editingContent
        ]);
    }

    public function updateDiscussion()
    {
        if (auth()->user()->id != $this->discussion->user_id) {
            Notification::make()
                ->title(trans('discussions::alert.danger.reason.update_post'))
                ->warning()
                ->send();
            return;
        }

        $state = $this->form->getState();
        $this->editingContent = $state['content'];

        $rules = [
            'editingTitle' => 'required|min:6',
            'editingContent' => 'required|min:6',
        ];
        
        $validator = Validator::make($this->getDataForValidation($rules), $rules);

        if ($validator->fails()) {

            Notification::make()
                ->title('Validation error')
                ->danger()
                ->body($validator->errors()->first())
                ->send();
            return;
        }

        $this->discussion->title = $this->editingTitle;
        $this->discussion->content = $this->editingContent;
        $this->discussion->save();

        $this->editing = false;
    }

    public function cancelEditing()
    {
        $this->editing = false;
    }

    public function toggleNotification()
    {
        if ($this->discussion->subscribers->contains(auth()->user()->id)) {
            $this->discussion->subscribers()->detach(auth()->user()->id);
            $this->user_subscribed = false;
            Notification::make()
                ->title(trans('discussions::alert.success.reason.unsubscribed_from_discussion'))
                ->success()
                ->send();
            return;
        }

        $this->discussion->subscribers()->attach(auth()->user()->id);
        $this->user_subscribed = true;
        $this->getSubscribers();

        Notification::make()
            ->title(trans('discussions::alert.success.reason.subscribed_to_discussion'))
            ->success()
            ->send();
        return;
    }

    public function getSubscribers()
    {
        $this->subscribers = $this->discussion->users()->get();
    }

    public function render()
    {
        $layout = (auth()->guest()) ? 'theme::components.layouts.marketing' : 'theme::components.layouts.app';
        return view('discussions::livewire.discussion')->layout($layout);
    }
}
