<?php

return [
    'words' => [
        'cancel'  => 'Cancel',
        'delete'  => 'Delete',
        'report'  => 'Report',
        'save'    => 'Save',
        'edit'    => 'Edit',
        'comment'  => 'Comment',
        'create'  => 'Create',
        'submit'  => 'Submit',
        'yes'     => 'Yes',
        'no'      => 'No',
        'minutes' => '1 minute| :count minutes'
    ],

    'discussion' => [
        'new'            => 'New ' . trans('discussions::text.titles.discussion'),
        'all'            => 'All ' . trans('discussions::text.titles.discussion'),
        'posted_by'      => 'Posted by',
        'head_details'   => 'Posted in Category',
        'no_discussions' => 'No discussions found',
        'load_more'      => 'Load More',
        'no_category'    => 'No Category Specified',
        'no_participants'=> 'No Participants'

    ],
    'response' => [
        'confirm'     => 'Are you sure you want to delete this response?',
        'yes_confirm' => 'Yes Delete It',
        'no_confirm'  => 'No Thanks',
        'submit'      => 'Submit response',
        'update'      => 'Update Response',
    ],

    'editor' => [
        'title'               => 'Title of ' . trans('discussions::text.titles.discussion'),
        'content'             => 'Start Writing Your ' . ucfirst(trans('discussions::text.titles.discussion')) . ' Here',
        'reply'               => 'Write a Reply',
        'select'              => 'Select a Category',
        'tinymce_placeholder' => 'Type Your ' . trans('discussions::text.titles.discussion') . ' Here...',
        'select_color_text'   => 'Select a Color for this ' . trans('discussions::text.titles.discussion') . ' (optional)',
    ],

    'email' => [
        'notify'       => 'Notify me when someone replies',
        'dont_notify' => 'Don\'t notify me when someone replies',
    ],

    'auth' => 'Please <a href="/:home/login">login</a>
                or <a href="/:home/register">register</a>
                to leave a response.',

];
