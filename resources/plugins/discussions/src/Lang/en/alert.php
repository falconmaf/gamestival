<?php

return [
    'success' => [
        'title'  => 'Well done!',
        'reason' => [
            'submitted_to_post'       => 'Successfully posted to ' . mb_strtolower(trans('discussions::text.titles.discussion')) . '.',
            'updated_post'            => 'Successfully updated the ' . mb_strtolower(trans('discussions::text.titles.discussion')) . '.',
            'destroy'            => 'Successfully deleted ' . mb_strtolower(trans('discussions::text.titles.discussion')) . '.',
            'destroy_from_discussion' => 'Successfully deleted the response from the ' . mb_strtolower(trans('discussions::text.titles.discussion')) . '.',
            'created_discussion'      => 'Successfully created a new ' . mb_strtolower(trans('discussions::text.titles.discussion')) . '.',
            'unsubscribed_from_discussion' => 'You will no longer recieve notifications for this ' . mb_strtolower(trans('discussions::text.titles.discussion')) . '.',
            'subscribed_to_discussion' => 'You will now receive notifications for this ' . mb_strtolower(trans('discussions::text.titles.discussion')) . '.',
        ],
    ],
    'info' => [
        'title' => 'Heads Up!',
    ],
    'warning' => [
        'title' => 'Wuh Oh!',
    ],
    'danger'  => [
        'title'  => 'Oh Snap!',
        'reason' => [
            'errors'            => 'Please fix the following errors:',
            'prevent_spam'      => 'To prevent spam, please allow at least :minutes minute before starting a new discussion.',
            'trouble'           => 'Sorry, there seems to have been a problem submitting your response.',
            'update_post'       => 'Nah ah ah... Could not update your response. Make sure you\'re not doing anything shady.',
            'destroy'      => 'Nah ah ah... Could not delete the response. Make sure you\'re not doing anything shady.',
            'create_discussion' => 'Whoops :( There seems to be a problem creating your ' . mb_strtolower(trans('discussions::text.titles.discussion')) . '.',
            'title_required'    => 'Please write a title',
            'title_min'            => 'The title has to have at least :min characters.',
            'title_max'            => 'The title has to have no more than :max characters.',
            'content_required'  => 'Please write some content',
            'content_min'          => 'The content has to have at least :min characters',
            'category_required' => 'Please choose a category',



        ],
    ],
];
