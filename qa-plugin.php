<?php

if (!defined('QA_VERSION')) { // don't allow this page to be requested directly from browser
    header('Location: ../../');
    exit;
}

qa_register_plugin_phrases('lang/show-online-user-count-lang-*.php', 'show_online_user_count_lang');

qa_register_plugin_module('page', 'show-online-user-count-page.php', 'show_online_user_count_page', 'Show online user count');
qa_register_plugin_module('widget', 'show-online-user-count-widget.php', 'show_online_user_count_widget', 'Show online user count');
