CREATE TABLE IF NOT EXISTS {$DBPREFIX$}{$PACKAGENAME$}_{$MODULENAME$}_blocks_register (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID#',
  form_id INT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Register form ID#',
  email_as_login TINYINT(1) UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Use e-mail as login',
  notify_about_edit TINYINT(1) UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Notify admin about profile edit',
  allow_edit_social TINYINT(1) UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Allow to edit social networks',
  activation_type TINYINT(1) UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Activation type: 0 - by admin, 1 - by user, 2 - already active',
  allow_to TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'Allow block to: -1 - unregistered, 0 - all, 1 - registered',
  redirect_url VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'Redirect unallowable users to',

  PRIMARY KEY (id),
  KEY (form_id)
) COMMENT='Register blocks';

CREATE TABLE IF NOT EXISTS {$DBPREFIX$}{$PACKAGENAME$}_{$MODULENAME$}_blocks_login (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID#',
  email_as_login TINYINT(1) UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Show e-mail as login',
  social_login_type TINYINT(1) UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Social networks log-in type: 0 - none, 1 - only registered, 2 - quick register',
  password_save_type TINYINT(1) UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Password save type: 0 - none, 1 - checkbox "save password", 2 - checkbox "foreign computer"',
  
  PRIMARY KEY (id)
) COMMENT='Log in blocks';

CREATE TABLE IF NOT EXISTS {$DBPREFIX$}{$PACKAGENAME$}_{$MODULENAME$}_blocks_recovery (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID#',
  notification_id INT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Recovery notification ID#',
  
  PRIMARY KEY (id),
  KEY (notification_id)
) COMMENT='Recovery blocks';

