<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Spaced Repetition Intervals
    |--------------------------------------------------------------------------
    |
    | Days after a topic is studied on which a review is scheduled by
    | default (D+1, D+7, D+15, D+30, D+60, D+90). Users may override this
    | with their own set of intervals via a ReviewPreference record.
    |
    */

    'default_intervals' => [1, 7, 15, 30, 60, 90],

];
