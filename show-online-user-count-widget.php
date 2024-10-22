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

class show_online_user_count_widget
{
    private $urltoroot;

    public function load_module($directory, $urltoroot)
    {
        $this->urltoroot = $urltoroot;
    }

    function allow_template($template)
    {
        $allow = false;

        switch ($template) {
            case 'account':
            case 'activity':
            case 'admin':
            case 'ask':
            case 'categories':
            case 'custom':
            case 'favorites':
            case 'feedback':
            case 'hot':
            case 'ip':
            case 'login':
            case 'message':
            case 'qa':
            case 'question':
            case 'questions':
            case 'register':
            case 'search':
            case 'tag':
            case 'tags':
            case 'unanswered':
            case 'updates':
            case 'user':
            case 'users':
                $allow = true;
                break;
        }

        return $allow;
    }

    function allow_region($region)
    {
        $allow = false;

        switch ($region) {

            case 'side':
                $allow = true;
                break;
            case 'main':
            case 'full':
                break;
        }

        return $allow;
    }

    function inc_page_view_count()
    {
        if (qa_opt('TODAY_DATE') != date("Y-m-d")) {
            qa_opt('YESTERDAY_VISITOR_COUNT', (int)qa_opt('TODAY_VISITOR_COUNT'));
            qa_opt('TODAY_VISITOR_COUNT', '0');
            qa_opt('TODAY_DATE', date("Y-m-d"));
        }
        qa_opt('TODAY_VISITOR_COUNT', ((int)qa_opt('TODAY_VISITOR_COUNT') + 1));
        qa_opt('TOTAL_VISITOR_COUNT', ((int)qa_opt('TOTAL_VISITOR_COUNT') + 1));
    }

    function clean_offline_user()
    {
        $temp = (int)qa_opt('activity_time_out');
        $activity_time_out = ($temp > 0 ? $temp : 5) * 60;
        $timestamp = strtotime("now") - $activity_time_out;
        $query = 'DELETE FROM `^as_online_users` WHERE last_activity<"' . date('Y-m-d H:i:s', $timestamp) . '"';
        qa_db_query_sub($query);
    }

    function activity_update()
    {
        $this->inc_page_view_count();
        $logged_userid = qa_get_logged_in_userid();
        $ipAddress = bin2hex(@inet_pton(qa_remote_ip_address()));
        $currentDate = date('Y-m-d H:i:s');

        $activity_id = $this->getActivityId($logged_userid, $ipAddress);
        $this->insertOrUpdateActivity($activity_id, $currentDate, $logged_userid, $ipAddress);
    }

    /**
     * @param $logged_userid
     * @param $ipAddress
     *
     * @return string|null
     */
    private function getActivityId($logged_userid, $ipAddress)
    {
        if (isset($logged_userid)) {
            $sql = 'SELECT `id` FROM `^as_online_users` WHERE `user_id` = $';
            $params = [$logged_userid];
        } else {
            $sql = 'SELECT `id` FROM `^as_online_users` WHERE `ip` = UNHEX($)';
            $params = [$ipAddress];
        }

        $activity_id = qa_db_read_one_value(qa_db_query_sub_params($sql, $params), true);

        return $activity_id;
    }

    /**
     * @param $activity_id
     * @param $currentDate
     * @param $logged_userid
     * @param $ipAddress
     */
    private function insertOrUpdateActivity($activity_id, $currentDate, $logged_userid, $ipAddress)
    {
        if (isset($activity_id)) {
            $sql = 'UPDATE `^as_online_users` SET `last_activity` = $ WHERE `id` = #';
            $params = [$currentDate, $activity_id];
        } else {
            $sql = 'INSERT INTO `^as_online_users` (`user_id`, `ip`, `last_activity`) VALUES ($, UNHEX($), $)';
            $params = [$logged_userid, $ipAddress, $currentDate];
        }

        qa_db_query_sub_params($sql, $params);
    }

    function output_widget($region, $place, $themeobject, $template, $request, $qa_content)
    {
        global $relative_url_prefix;

        $this->clean_offline_user();
        $this->activity_update();
        $query = 'SELECT COUNT(*) FROM `^as_online_users` WHERE `user_id` IS NOT NULL';
        $resultDb = qa_db_query_sub($query);
        $online_member_count = qa_db_read_one_assoc($resultDb, true);
        $query = 'SELECT COUNT(*) FROM `^as_online_users` WHERE `user_id` IS NULL';
        $resultDb = qa_db_query_sub($query);
        $online_guest_count = qa_db_read_one_assoc($resultDb, true);
        $total_onilne_html = '<span class="show-online-user-count-data">' . ($online_member_count['COUNT(*)'] + $online_guest_count['COUNT(*)']) . '</span>';
        $online_quest_html = '<span class="show-online-user-count-data">' . $online_guest_count['COUNT(*)'] . '</span>';
        $online_member_html = '<span class="show-online-user-count-data">' . $online_member_count['COUNT(*)'] . '</span>';
        $tempStr = str_replace('^1', $online_member_html, qa_lang_html('show_online_user_count_lang/online_guest_member'));
        $tempStr = str_replace('^2', $online_quest_html, $tempStr);

        $themeobject->output(sprintf('<link rel="stylesheet" href="%scss/style.css">', $this->urltoroot));
        $themeobject->output('<div class="show-online-user-count-total">');
        $themeobject->output(str_replace('^', $total_onilne_html, qa_lang_html('show_online_user_count_lang/total_online_users')));
        $themeobject->output('</div>');
        $themeobject->output('<div class="show-online-user-count-info">');
        $themeobject->output($tempStr);
        $themeobject->output('</div>');
        if (qa_opt('show_online_user_list')) {
            $query = 'SELECT `u`.`handle` FROM `^users` `u`, `^as_online_users` `ou` WHERE `u`.`userid` = `ou`.`user_id`';
            $resultDb = qa_db_query_sub($query);
            if (mysqli_num_rows($resultDb) > 0) {
                $html = '';
                while (list($user_name) = mysqli_fetch_row($resultDb)) {
                    $html .= qa_get_one_user_html($user_name, true, $relative_url_prefix) . '-';
                }
                $html = substr($html, 0, -1);
                $themeobject->output('<div class="show-online-user-list">');
                $themeobject->output('<div class="show-online-user-list_title">', qa_lang_html('show_online_user_count_lang/online_user_list'), '</div>');
                $themeobject->output('<div class="show-online-user-list-users">', $html, '</div>');
                $themeobject->output('</div>');
            }
        }
        if (qa_opt('show_site_visitor_count')) {
            $today_count = '<span class="show-stats-list-data">' . ((int)qa_opt('TODAY_VISITOR_COUNT')) . '</span>';
            $yesterday_count = '<span class="show-stats-list-data">' . ((int)qa_opt('YESTERDAY_VISITOR_COUNT')) . '</span>';
            $total_count = '<span class="show-stats-list-data">' . ((int)qa_opt('TOTAL_VISITOR_COUNT')) . '</span>';
            $themeobject->output('<div class="show-stats-list">');
            $themeobject->output('<div class="show-stats-list-today">');
            $themeobject->output(str_replace('^', $today_count, qa_lang_html('show_online_user_count_lang/today_count')));
            $themeobject->output('</div>');
            $themeobject->output('<div class="show-stats-list-yesterday">');
            $themeobject->output(str_replace('^', $yesterday_count, qa_lang_html('show_online_user_count_lang/yesterday_count')));
            $themeobject->output('</div>');
            $themeobject->output('<div class="show-stats-list-total">');
            $themeobject->output(str_replace('^', $total_count, qa_lang_html('show_online_user_count_lang/total_count')));
            $themeobject->output('</div>');
            $themeobject->output('</div>');
        }
    }
}
