<?php
/**
 * PHP Server Monitor
 * Monitor your servers and websites.
 *
 * This file is part of PHP Server Monitor.
 * PHP Server Monitor is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * PHP Server Monitor is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with PHP Server Monitor.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package     phpservermon
 * @author      manhere <https://github.com/manhere>
 * @copyright   Copyright (c) 2008-2017 Pepijn Over <pep@mailbox.org>
 * @license     http://www.gnu.org/licenses/gpl.txt GNU GPL v3
 * @version     Release: @package_version@
 * @link        http://www.phpservermonitor.org/
 **/

$sm_lang = array(
	'name' => '中文 - Chinese',
	'locale' => array('zh_CN.UTF-8', 'zh_CN', 'chinese', 'chinese-cn'),
	'locale_tag' => 'zh',
	'locale_dir' => 'ltr',
	'system' => array(
		'title' => 'Server Monitor',
		'install' => '安装',
		'action' => '操作',
		'save' => '保存',
		'edit' => '编辑',
		'delete' => '删除',
		'date' => '日期',
		'message' => '消息',
		'yes' => '是',
		'no' => '否',
		'insert' => '新增',
		'add_new' => '添加',
		'update_available' => '发现新版本({version}) <a href="http://www.phpservermonitor.org" target="_blank">http://www.phpservermonitor.org</a>.',
		'back_to_top' => '返回顶部',
		'go_back' => '后退',
		'ok' => '确认',
		'cancel' => '取消',
		// date/time format according the strftime php function format parameter http://php.net/manual/function.strftime.php
		'short_day_format' => '%B %e',
		'long_day_format' => '%B %e, %Y',
		'yesterday_format' => '昨日 %k:%M',
		'other_day_format' => '%A %k:%M',
		'never' => '从未',
		'hours_ago' => '%d 小时前',
		'an_hour_ago' => '1小时前',
		'minutes_ago' => '%d 分钟前',
		'a_minute_ago' => '1分钟前',
		'seconds_ago' => '%d 秒前',
		'a_second_ago' => '刚刚',
		'year' => 'year',
		'years' => 'years',
		'month' => 'month',
		'months' => 'months',
		'day' => 'day',
		'days' => 'days',
		'hour' => 'hour',
		'hours' => 'hours',
		'minute' => 'minute',
		'minutes' => 'minutes',
		'second' => 'second',
		'seconds' => 'seconds',
	),
	'menu' => array(
		'config' => '设置',
		'server' => '监控',
		'server_log' => '日志',
		'server_status' => '状态',
		'server_update' => '刷新',
		'user' => '用户',
		'help' => '帮助',
	),
	'users' => array(
		'user' => '用户',
		'name' => '名称',
		'user_name' => '用户名',
		'password' => '密码',
		'password_repeat' => '重复密码',
		'password_leave_blank' => '留空为不修改',
		'level' => '等级',
		'level_10' => '超级管理员',
		'level_20' => '普通用户',
		'level_description' => '<b>超级管理员</b> 拥有所有权限: 管理服务器, 用户 以及修改设置.<br/><b>普通用户</b> 只能查看及更新自己名下所属的服务器.',
		'mobile' => '手机',
		'email' => '邮件',
		'pushover' => 'Pushover',
		'pushover_description' => 'Pushover 是第三方用于实时通知的服务(收费). 详情见 <a href="https://pushover.net/" target="_blank">Pushover 官网</a>.',
		'pushover_key' => 'Pushover Key',
		'pushover_device' => 'Pushover Device',
		'pushover_device_description' => '要发送信息的设备名. 留空则发送到所有设备.',
		'delete_title' => '删除用户',
		'delete_message' => '确认删除用户 \'%1\'?',
		'deleted' => '用户已删除.',
		'updated' => '用户已更新.',
		'inserted' => '用户已添加.',
		'profile' => '个人资料',
		'profile_updated' => '个人资料已更新.',
		'error_user_name_bad_length' => '用户名长度必须为2-64个字符.',
		'error_user_name_invalid' => '用户名只允许使用字母 (a-z, A-Z), 数字 (0-9), 點（。） 及下划线 (_).',
		'error_user_name_exists' => '该用户名已存在.',
		'error_user_email_bad_length' => '电子邮箱长度必须为5-255个字符.',
		'error_user_email_invalid' => '无效的邮箱地址.',
		'error_user_level_invalid' => '该用户等级无效.',
		'error_user_no_match' => '该用户名不存在.',
		'error_user_password_invalid' => '密码无效.',
		'error_user_password_no_match' => '密码不符.',
	),
	'log' => array(
		'title' => '日志概览',
		'type' => '类型',
		'status' => '状态',
		'email' => '邮件',
		'sms' => '短信',
		'pushover' => 'Pushover',
		'no_logs' => '没有日志',
		'clear' => 'Clear log',
		'delete_title' => 'Delete log',
		'delete_message' => 'Are you sure you want to delete <b>all</b> logs?',
	),
	'servers' => array(
		'server' => '业务',
		'status' => '状态',
		'label' => '标签',
		'domain' => 'URL/IP',
		'timeout' => '超时时间',
		'timeout_description' => '等待服务器响应的时间.',
		'authentication_settings' => '访问权限设置 (可选)',
		'website_username' => '用户名',
		'website_username_description' => '网站分配的用户名.',
		'website_password' => '密码',
		'website_password_description' => '网站分配的密码，密码将会加密存放',
		'fieldset_monitoring' => '通知',
		'fieldset_permissions' => '权限',
		'port' => '端口',
		'custom_port' => '指定端口',
		'popular_ports' => '默认端口',
		'please_select' => '请选择',
		'type' => '类型',
		'type_website' => '网站',
		'type_service' => '服务',
		'pattern' => '字符串/正则匹配',
		'pattern_description' => '如果在网站上未找到对应匹配内容, 则标记该网站为离线. 支持正则表达式.',
		'last_check' => '最后检查',
		'last_online' => '最后在线',
		'last_offline' => 'Last offline',
		'monitoring' => '监控',
		'no_monitoring' => '未监控',
		'email' => '邮件',
		'send_email' => '发送邮件',
		'sms' => '短信',
		'send_sms' => '发送短信',
		'pushover' => 'Pushover',
		'users' => '用户',
		'delete_title' => '删除服务器',
		'delete_message' => '确认删除服务器 \'%1\'?',
		'deleted' => '服务器已删除.',
		'updated' => '服务器已更新.',
		'inserted' => '服务器已添加.',
		'latency' => '延迟',
		'latency_max' => '最大延迟',
		'latency_min' => '最小延迟',
		'latency_avg' => '平均延迟',
		'uptime' => '在线时长',
		'year' => '年',
		'month' => '月',
		'week' => '周',
		'day' => '日',
		'hour' => '小时',
		'warning_threshold' => '报警阈值',
		'warning_threshold_description' => '失败达到多少次数则标记为离线.',
		'chart_last_week' => '上周',
		'chart_history' => '更早',
		// Charts date format according jqPlot date format  http://www.jqplot.com/docs/files/plugins/jqplot-dateAxisRenderer-js.html
		'chart_day_format' => '%Y-%m-%d',
		'chart_long_date_format' => '%Y-%m-%d %H:%M:%S',
		'chart_short_date_format' => '%m/%d %H:%M',
		'chart_short_time_format' => '%H:%M',
		'warning_notifications_disabled_sms' => '短信通知不可用.',
		'warning_notifications_disabled_email' => 'Email 通知不可用.',
		'warning_notifications_disabled_pushover' => 'Pushover 通知不可用.',
		'error_server_no_match' => '服务器不存在.',
		'error_server_label_bad_length' => '标签 长度要求 1 ~ 255 字符.',
		'error_server_ip_bad_length' => 'URL / IP 长度要求 1 ~ 255 字符.',
		'error_server_ip_bad_service' => '非法 IP.',
		'error_server_ip_bad_website' => '非法 URL.',
		'error_server_type_invalid' => '非法选项.',
		'error_server_warning_threshold_invalid' => '报警阈值为大于 0 的数字.',
	),
	'config' => array(
		'general' => '通用',
		'language' => '语言',
		'show_update' => '每周检查更新?',
		'password_encrypt_key' => '加密密钥',
		'password_encrypt_key_note' => '该密钥用于加密访问 URL 时的用户名和密码. 如果需要修改密钥，请同步修改以前的监控业务，确认密钥匹配!',
		'proxy' => '使用代理',
		'proxy_url' => '代理IP',
		'proxy_user' => '用户名',
		'proxy_password' => '密码',
		'email_status' => '允许发送邮件?',
		'email_from_email' => '发件人地址',
		'email_from_name' => '发件人名称',
		'email_smtp' => '使用SMTP发送',
		'email_smtp_host' => 'SMTP主机',
		'email_smtp_port' => 'SMTP端口',
		'email_smtp_security' => 'SMTP 设置',
		'email_smtp_security_none' => '空',
		'email_smtp_username' => 'SMTP用户名',
		'email_smtp_password' => 'SMTP密码',
		'email_smtp_noauth' => '留空为无验证',
		'sms_status' => '允许发送短信SMS?',
		'sms_gateway' => '短信SMS发送网关',
		'sms_gateway_username' => 'SMS网关用户名',
		'sms_gateway_password' => 'SMS网关密码',
		'sms_from' => '发信人电话号',
		'pushover_status' => '允许 Pushover 通知',
		'pushover_description' => 'Pushover 是第三方用于实时通知的服务(收费). 详情见 <a href="https://pushover.net/" target="_blank">Pushover 官网</a>.',
		'pushover_clone_app' => '点此创建 Pushover App',
		'pushover_api_token' => 'Pushover API Token',
		'pushover_api_token_description' => '请先 <a href="%1$s" target="_blank">注册Pushover</a> 并获取 Api Token.',
		'alert_type' => '如果想要收到提醒请选中此项.',
		'alert_type_description' => '<b>状态变化:</b> '.
			'业务 online -> offline 或 offline -> online 的状态变化将会收到提醒.<br/>'.
			'<br /><b>离线状态:</b> '.
			'服务器首次发生离线状态将会收到提醒 ，如：'.
			'cronjob 设定为15分钟执行一次， 服务器从1:00-6:00一直处于当状态'.
			'那么你将于1:00首次发现脱机时收到一条提醒，之后不会重复提醒.<br/>'.
			'<br><b>总是提醒:</b> '.
			'每次脚本执行或站点离线(即使站点离线很久已提醒过)均发送提醒.',
		'alert_type_status' => '状态变化',
		'alert_type_offline' => '离线状态',
		'alert_type_always' => '总是提醒',
		'alert_proxy' => '代理只用于 URL 监控，如没有用户名密码，请留空',
		'alert_proxy_url' => '<b>格式:</b> Host:Port',
		'log_status' => '状态记录',
		'log_status_description' => '如果状态记录设置为开, 提醒发送时均会保存记录.',
		'log_email' => '记录脚本所发邮件?',
		'log_sms' => '记录脚本所发短信?',
		'log_pushover' => '记录脚本所发pushover消息?',
		'updated' => '设置已更新.',
		'tab_email' => '邮件发送设置',
		'tab_sms' => '短信发送设置',
		'tab_pushover' => 'Pushover',
		'settings_email' => '邮件发送设置',
		'settings_sms' => '短信发送设置',
		'settings_pushover' => 'Pushover 设置',
		'settings_notification' => '提醒设置',
		'settings_log' => '日志设置',
		'settings_proxy' => '代理设置',
		'auto_refresh' => '自动刷新',
		'auto_refresh_servers' =>
			'自动刷新服务器页.<br/>'.
			'<span class="small">'.
			'单位为秒, 设置为 0 则不自动刷新.'.
			'</span>',
		'seconds' => '秒',
		'test' => '测试',
		'test_email' => '将发送一封邮件到您账户设置的邮件地址.',
		'test_sms' => '将发送一封短信到您账户设置的手机号码.',
		'test_pushover' => '将发送一条 Pushover 通知到您账户设置的 key/device 设备上.',
		'send' => '发送',
		'test_subject' => '测试',
		'test_message' => '测试信息',
		'email_sent' => '发送邮件',
		'email_error' => '发送出错',
		'sms_sent' => '发送短信',
		'sms_error' => '短信发送出错 %s',
		'sms_error_nomobile' => '无法发送短信: 您的账号未设置有效手机号码.',
		'pushover_sent' => '发送Pushover通知',
		'pushover_error' => 'Pushover通知发送出错: %s',
		'pushover_error_noapp' => 'Pushover通知发送出错: no Pushover App API token found in the global configuration.',
		'pushover_error_nokey' => 'Pushover通知无法发送: no Pushover key found in your profile.',
		'log_retention_period' => '日志保留时长',
		'log_retention_period_description' => '日志存档保留时间，0为禁用日志清理',
		'log_retention_days' => '天',
	),
	// for newlines in the email messages use <br/>
	'notifications' => array(
		'off_sms' => '监控项 \'%LABEL%:%PORT%\' 异常. %ERROR% %IP%',
		'off_email_subject' => 'IMPORTANT: 服务器 \'%LABEL%\' 宕机',
		'off_email_body' => "无法连接到以下服务器:<br/><br/>服务器: %LABEL%<br/>IP: %IP%<br/>Port: %PORT%<br/>错误: %ERROR%<br/>日期: %DATE%",
		'off_pushover_title' => '服务器 \'%LABEL%\' 宕机',
		'off_pushover_message' => "无法连接到以下服务器:<br/><br/>服务器: %LABEL%<br/>IP: %IP%<br/>Port: %PORT%<br/>错误: %ERROR%<br/>日期: %DATE%",
		'on_sms' => '服务器 \'%LABEL%\' 运行中: ip=%IP%, port=%PORT%',
		'on_email_subject' => 'IMPORTANT: 服务器 \'%LABEL%\' 运行中',
		'on_email_body' => "服务器 '%LABEL%' 恢复运行:<br/><br/>服务器: %LABEL%<br/>IP: %IP%<br/>Port: %PORT%<br/>日期: %DATE%",
		'on_pushover_title' => '服务器 \'%LABEL%\' 运行中',
		'on_pushover_message' => "服务器 '%LABEL%' 恢复运行:<br/><br/>服务器: %LABEL%<br/>IP: %IP%<br/>Port: %PORT%<br/>日期: %DATE%",
	),
	'login' => array(
		'welcome_usermenu' => '欢迎, %user_name%',
		'title_sign_in' => '请登录',
		'title_forgot' => '忘记密码?',
		'title_reset' => '重设密码',
		'submit' => '提交',
		'remember_me' => '记住我',
		'login' => '登录',
		'logout' => '注销',
		'username' => '用户名',
		'password' => '密码',
		'password_repeat' => '重复密码',
		'password_forgot' => '忘记密码?',
		'password_reset' => '重设密码',
		'password_reset_email_subject' => '重设你的密码',
		'password_reset_email_body' => '点击以下链接重设密码. 链接1小时内有效.<br/><br/>%link%',
		'error_user_incorrect' => '该用户不存在.',
		'error_login_incorrect' => '登录信息不正确.',
		'error_login_passwords_nomatch' => '密码不符.',
		'error_reset_invalid_link' => '重设密码链接无效.',
		'success_password_forgot' => '密码重设邮件已发送.',
		'success_password_reset' => '密码重设成功.请登录.',
	),
	'error' => array(
		'401_unauthorized' => '未授权的请求',
		'401_unauthorized_description' => '未授权的请求.',
	),
);
