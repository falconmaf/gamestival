<?php

namespace Wave\Plugins\Discussions\Components;

use Wave\Plugins\Discussions\Models\Models;
use Filament\Notifications\Notification;
use Filament\Forms\Form;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\MarkdownEditor;
use Livewire\Component;
use Validator;

class DiscussionPosts extends Component implements HasForms
{
    use InteractsWithForms;

    public ?array $data = [];
    public $discussion;
    public $editingPostId = null;
    public $editedContent;
    public $loadMore = 5;

    public $listeners = [
        'postAdded' => '$refresh',
    ];

    public function mount($discussion)
    {
        $this->discussion = $discussion;
        $this->form->fill();
    }

    public function loadMore()
    {
        $this->loadMore = $this->loadMore + 5;
    }

    public function delete($id)
    {
        if (auth()->user()->id != Models::post()->find($id)->user_id) {
            Notification::make()
                ->title(trans('discussions::alert.danger.reason.destroy'))
                ->success()
                ->send();
            return;
        }
        Models::post()->find($id)->delete();
    }

    public function form(Form $form): Form
    {
        $editor = match(config('discussions.editor')){
            'textarea' => Textarea::make('content')->rows(8),
            'richeditor' => RichEditor::make('content'),
            'markdown' => MarkdownEditor::make('content')
        };

        return $form
            ->schema([
                $editor
                    ->label(false)
                    ->placeholder(trans('discussions::messages.editor.content'))
            ])
            ->statePath('data');
    }

    public function edit($id)
    {
        if (auth()->user()->id != Models::post()->find($id)->user_id) {
            Notification::make()
                ->title(trans('discussions::alert.danger.reason.update_post'))
                ->warning()
                ->send();
            return;
        }
        $this->editingPostId = $id;
        $this->editedContent = Models::post()->find($id)->content;

        $this->form->fill([
            'content' => $this->editedContent
        ]);
    }

    public function cancelEdit()
    {
        $this->editingPostId = null;
        $this->editedContent = null;
    }

    public function update($id)
    {
        $state = $this->form->getState();
        $this->editedContent = $state['content'];
        

        $post = Models::post()->where('id', $id)->first();

        if (!$post) {
            return;
        }

        if (auth()->user()->id != $post->user_id) {
            Notification::make()
                ->title(trans('discussions::alert.danger.reason.update_post'))
                ->warning()
                ->send();
            return;
        }

        $rules = [
            'editedContent' => 'required',
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

        $post->update([
            'content' => $this->editedContent,
        ]);

        $this->editingPostId = null;

        return;
    }

    public function render()
    {
        $posts = Models::post()->where('discussion_id', $this->discussion->id)->orderBy('created_at', 'asc')->paginate($this->loadMore);

        $layout = (auth()->guest()) ? 'theme::components.layouts.marketing' : 'theme::components.layouts.app';
        return view('discussions::livewire.discussion-posts', compact('posts'))->layout($layout);
    }
}
