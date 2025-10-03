<?php
/**
 * Plugin Name: MainWP Pro Reports Extension - All Users
 * Plugin URI: https://mainwp.com
 * Description: Adds a custom token to MainWP Pro Reports to display all users from child sites in Pro Reports.
 * Version: 1.0.0
 * Author: Matt Wilks
 * License: GPL v2 or later
 */

if (!defined('ABSPATH')) {
    exit;
}

class MainWP_Pro_Reports_All_Users {
    private static $instance = null;
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        add_filter('mainwp_pro_reports_tokens_list', array($this, 'register_token'));
        add_filter('mainwp_pro_reports_replace_tokens', array($this, 'replace_tokens'), 10, 2);
    }

    public function register_token($tokens) {
        if (!is_array($tokens)) {
            $tokens = array();
        }
        $tokens['[allusers]'] = esc_html__('List all users on the site');
        $tokens['[allusers.table]'] = esc_html__('Display all users in a table format');
        return $tokens;
    }

    public function replace_tokens($content, $website) {
        if (strpos($content, '[allusers]') === false && strpos($content, '[allusers.table]') === false) {
            return $content;
        }
        try {
            $users = array();
            $site_id = null;
            if (is_object($website) && isset($website->id)) {
                $site_id = $website->id;
            } elseif (is_array($website) && isset($website['id'])) {
                $site_id = $website['id'];
            }
            if ($site_id && class_exists('MainWP_DB')) {
                $db = call_user_func(array('MainWP_DB', 'instance'));
                $full_website = $db->get_website_by_id($site_id);
                if ($full_website && isset($full_website->users) && !empty($full_website->users)) {
                    $decoded = json_decode($full_website->users);
                    if (is_array($decoded)) {
                        $users = $decoded;
                    }
                }
            }
            if (!empty($users)) {
                if (strpos($content, '[allusers.table]') !== false) {
                    $content = str_replace('[allusers.table]', $this->format_users_table($users), $content);
                }
                if (strpos($content, '[allusers]') !== false) {
                    $content = str_replace('[allusers]', $this->format_users_list($users), $content);
                }
            } else {
                $content = str_replace(array('[allusers]', '[allusers.table]'), 'No users found', $content);
            }
        } catch (Exception $e) {
            $content = str_replace(array('[allusers]', '[allusers.table]'), 'Error fetching users', $content);
        }
        return $content;
    }

    private function format_users_list($users) {
        $output = '';
        foreach ($users as $user) {
            $login = isset($user->login) ? $user->login : (isset($user['login']) ? $user['login'] : (isset($user->user_login) ? $user->user_login : (isset($user['user_login']) ? $user['user_login'] : '')));
            $email = isset($user->email) ? $user->email : (isset($user['email']) ? $user['email'] : (isset($user->user_email) ? $user->user_email : (isset($user['user_email']) ? $user['user_email'] : '')));
            if (isset($user->role) && !empty($user->role)) {
                $role_names = is_array($user->role) ? implode(', ', $user->role) : $user->role;
            } elseif (isset($user->roles) && !empty($user->roles)) {
                $role_names = is_array($user->roles) ? implode(', ', $user->roles) : $user->roles;
            } else {
                $role_names = 'No role';
            }
            $output .= sprintf(
                "%s (%s) - %s\n",
                esc_html($login),
                esc_html($email),
                esc_html($role_names)
            );
        }
        return trim($output);
    }

    private function format_users_table($users) {
        $output = '<table class="mainwp-users-table" style="width: 100%; border-collapse: collapse; margin: 15px 0;">'
            . '<thead><tr style="background-color: #f8f9fa;">'
            . '<th style="padding: 8px; border: 1px solid #dee2e6; text-align: left;">Username</th>'
            . '<th style="padding: 8px; border: 1px solid #dee2e6; text-align: left;">Email</th>'
            . '<th style="padding: 8px; border: 1px solid #dee2e6; text-align: left;">Role(s)</th>'
            . '<th style="padding: 8px; border: 1px solid #dee2e6; text-align: left;">Registration Date</th>'
            . '</tr></thead><tbody>';
        foreach ($users as $user) {
            $login = isset($user->login) ? $user->login : (isset($user['login']) ? $user['login'] : (isset($user->user_login) ? $user->user_login : (isset($user['user_login']) ? $user['user_login'] : '')));
            $email = isset($user->email) ? $user->email : (isset($user['email']) ? $user['email'] : (isset($user->user_email) ? $user->user_email : (isset($user['user_email']) ? $user['user_email'] : '')));
            if (isset($user->role) && !empty($user->role)) {
                $role_names = is_array($user->role) ? implode(', ', $user->role) : $user->role;
            } elseif (isset($user->roles) && !empty($user->roles)) {
                $role_names = is_array($user->roles) ? implode(', ', $user->roles) : $user->roles;
            } else {
                $role_names = 'No role';
            }
            if (!empty($user->user_registered)) {
                $reg_date = date('Y-m-d', strtotime($user->user_registered));
            } elseif (!empty($user->registered)) {
                $reg_date = date('Y-m-d', strtotime($user->registered));
            } else {
                $reg_date = 'N/A';
            }
            $output .= sprintf(
                '<tr>'
                . '<td style="padding: 8px; border: 1px solid #dee2e6;">%s</td>'
                . '<td style="padding: 8px; border: 1px solid #dee2e6;">%s</td>'
                . '<td style="padding: 8px; border: 1px solid #dee2e6;">%s</td>'
                . '<td style="padding: 8px; border: 1px solid #dee2e6;">%s</td>'
                . '</tr>',
                esc_html($login),
                esc_html($email),
                esc_html($role_names),
                esc_html($reg_date)
            );
        }
        $output .= '</tbody></table>';
        return $output;
    }
}

function mainwp_pro_reports_all_users_plugin_init() {
    if (class_exists('MainWP_Pro_Reports_Extension')) {
        MainWP_Pro_Reports_All_Users::get_instance();
    }
}
add_action('init', 'mainwp_pro_reports_all_users_plugin_init', 0);
