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

if (!defined('QA_VERSION')) { // don't allow this page to be requested directly from browser
    header('Location: ../../');
    exit;
}

qa_register_plugin_phrases('lang/show-online-user-count-lang-*.php', 'show_online_user_count_lang');

qa_register_plugin_module('page', 'show-online-user-count-page.php', 'show_online_user_count_page', 'Show online user count');
qa_register_plugin_module('widget', 'show-online-user-count-widget.php', 'show_online_user_count_widget', 'Show online user count');
