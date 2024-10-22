<?php

/*
	Question2Answer (c) Gideon Greenspan

	https://www.question2answer.org/

	This program is free software; you can redistribute it and/or
	modify it under the terms of the GNU General Public License
	as published by the Free Software Foundation; either version 2
	of the License, or (at your option) any later version.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	More about this license: https://www.question2answer.org/license.php
*/

class show_online_user_count_page
{
    function init_queries($tableslc)
    {
        require_once QA_INCLUDE_DIR . 'app/users.php';
        require_once QA_INCLUDE_DIR . 'db/maxima.php';

        $queries = [];

        if (!in_array(qa_db_add_table_prefix('as_online_users'), $tableslc)) {
            $queries[] =
                'CREATE TABLE IF NOT EXISTS `^as_online_users` (' .
                '   `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,' .
                '   `user_id` ' . qa_get_mysql_user_column_type() . ',' .
                '   `ip` VARBINARY(16) NOT NULL,' .
                '   `last_activity` DATETIME NOT NULL,' .
                '   PRIMARY KEY (`id`),' .
                '   CONSTRAINT `^as_online_users_fk1` FOREIGN KEY (`user_id`) REFERENCES `^users` (`userid`) ON DELETE CASCADE' .
                ') ENGINE = InnoDB';
        }

        return $queries;
    }

    function admin_form()
    {
        require_once QA_INCLUDE_DIR . 'qa-util-sort.php';

        $saved = false;

        if (qa_clicked('SAVE_BUTTON')) {
            qa_opt('show_online_user_list', (int)qa_post_text('show_online_user_list_field'));
            qa_opt('activity_time_out', ((int)qa_post_text('activity_time_out_field') == '') ? 5 : (int)qa_post_text('activity_time_out_field'));
            qa_opt('show_site_visitor_count', (int)qa_post_text('show_site_visitor_count_field'));

            $saved = true;
        }

        $form = [
            'ok' => $saved ? qa_lang_html('show_online_user_count_lang/change_ok') : null,
            'fields' => [
                'question1' => [
                    'label' => qa_lang_html('show_online_user_count_lang/show_online_members'),
                    'type' => 'checkbox',
                    'value' => (int)qa_opt('show_online_user_list'),
                    'tags' => 'name="show_online_user_list_field"',
                ],

                'question2' => [
                    'label' => qa_lang_html('show_online_user_count_lang/show_site_visitor_count'),
                    'type' => 'checkbox',
                    'value' => (int)qa_opt('show_site_visitor_count'),
                    'tags' => 'name="show_site_visitor_count_field"',
                ],

                'question3' => [
                    'type' => 'number',
                    'label' => qa_lang_html('show_online_user_count_lang/activity_time_out'),
                    'value' => qa_html(qa_opt('activity_time_out')),
                    'tags' => 'name="activity_time_out_field"',
                ],

            ],

            'buttons' => [
                [
                    'label' => qa_lang_html('show_online_user_count_lang/save_button'),
                    'tags' => 'name="SAVE_BUTTON"',
                ],
            ],
        ];

        return $form;
    }
}
