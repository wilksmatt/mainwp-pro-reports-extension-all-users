<?php
/**
 * Plugin Name: MainWP Pro Reports Extension - All Users
 * Plugin URI: https://github.com/wilksmatt/mainwp-pro-reports-extension-all-users
 * Description: Adds a custom token to MainWP Pro Reports to display all users from child sites in Pro Reports.
 * Version: 1.0.0
 * Author: Matt Wilks
 * License: GPL v2 or later
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * MainWP_Pro_Reports_All_Users
 *
 * Singleton class to register and handle custom tokens for listing all users in MainWP Pro Reports.
 */
class MainWP_Pro_Reports_All_Users {
    /**
     * Holds the singleton instance.
     * @var MainWP_Pro_Reports_All_Users|null
     */
    private static $instance = null;
    
    /**
     * Get the singleton instance.
     * @return MainWP_Pro_Reports_All_Users
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Constructor. Registers the token list filter.
     */
    private function __construct() {
        add_filter('mainwp_pro_reports_tokens_list', array($this, 'register_token'));
    }

    /**
     * Register custom tokens for the Pro Reports token list.
     *
     * @param array $tokens
     * @return array
     */
    public function register_token($tokens) {
        if (!is_array($tokens)) {
            $tokens = array();
        }
        $tokens['[allusers]'] = esc_html__('List all users on the site');
        $tokens['[allusers.table]'] = esc_html__('Display all users in a table format');
        return $tokens;
    }

    /**
     * Format the users as a plain text list.
     *
     * @param array $users
     * @return string
     */
    public function format_users_list($users) {
        $output = '';
        foreach ($users as $user) {
            // Get username
            $login = isset($user->login) ? $user->login : (isset($user['login']) ? $user['login'] : (isset($user->user_login) ? $user->user_login : (isset($user['user_login']) ? $user['user_login'] : '')));
            // Get email
            $email = isset($user->email) ? $user->email : (isset($user['email']) ? $user['email'] : (isset($user->user_email) ? $user->user_email : (isset($user['user_email']) ? $user['user_email'] : '')));
            // Get role(s)
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

    /**
     * Format the users as an HTML table.
     *
     * @param array $users
     * @return string
     */
    public function format_users_table($users) {
        $output = '<table class="mainwp-users-table" style="width: 100%; border-collapse: collapse; margin: 15px 0;">'
            . '<thead><tr style="background-color: #f8f9fa;">'
            . '<th style="padding: 8px; border: 1px solid #dee2e6; text-align: left;">Username</th>'
            . '<th style="padding: 8px; border: 1px solid #dee2e6; text-align: left;">Email</th>'
            . '<th style="padding: 8px; border: 1px solid #dee2e6; text-align: left;">Role(s)</th>'
            . '<th style="padding: 8px; border: 1px solid #dee2e6; text-align: left;">Registration Date</th>'
            . '</tr></thead><tbody>';
        foreach ($users as $user) {
            // Get username
            $login = isset($user->login) ? $user->login : (isset($user['login']) ? $user['login'] : (isset($user->user_login) ? $user->user_login : (isset($user['user_login']) ? $user['user_login'] : '')));
            // Get email
            $email = isset($user->email) ? $user->email : (isset($user['email']) ? $user['email'] : (isset($user->user_email) ? $user->user_email : (isset($user['user_email']) ? $user['user_email'] : '')));
            // Get role(s)
            if (isset($user->role) && !empty($user->role)) {
                $role_names = is_array($user->role) ? implode(', ', $user->role) : $user->role;
            } elseif (isset($user->roles) && !empty($user->roles)) {
                $role_names = is_array($user->roles) ? implode(', ', $user->roles) : $user->roles;
            } else {
                $role_names = 'No role';
            }
            // Get registration date
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

/**
 * Initialize the plugin and register the singleton if MainWP Pro Reports Extension is active.
 */
function mainwp_pro_reports_all_users_plugin_init() {
    if (class_exists('MainWP_Pro_Reports_Extension')) {
        MainWP_Pro_Reports_All_Users::get_instance();
    }
}
add_action('init', 'mainwp_pro_reports_all_users_plugin_init', 0);

/**
 * Register and handle custom tokens for Pro Reports engine (for use in custom sections).
 *
 * @param array $parsed_other_tokens
 * @param object $report
 * @param object|array $website
 * @return array
 */
add_filter('mainwp_pro_reports_parsed_other_tokens', function($parsed_other_tokens, $report, $website) {
    // Ensure token arrays exist
    if (!isset($parsed_other_tokens['other_tokens'])) {
        $parsed_other_tokens['other_tokens'] = array();
    }
    if (!isset($parsed_other_tokens['other_tokens_data'])) {
        $parsed_other_tokens['other_tokens_data'] = array();
    }
    if (!isset($parsed_other_tokens['other_tokens']['body'])) {
        $parsed_other_tokens['other_tokens']['body'] = array();
    }
    if (!isset($parsed_other_tokens['other_tokens']['header'])) {
        $parsed_other_tokens['other_tokens']['header'] = array();
    }
    if (!isset($parsed_other_tokens['other_tokens_data']['body'])) {
        $parsed_other_tokens['other_tokens_data']['body'] = array();
    }
    if (!isset($parsed_other_tokens['other_tokens_data']['header'])) {
        $parsed_other_tokens['other_tokens_data']['header'] = array();
    }

    // Register tokens in both body and header
    $parsed_other_tokens['other_tokens']['body'][] = '[allusers]';
    $parsed_other_tokens['other_tokens']['body'][] = '[allusers.table]';
    $parsed_other_tokens['other_tokens']['header'][] = '[allusers]';
    $parsed_other_tokens['other_tokens']['header'][] = '[allusers.table]';

    // Fetch users JSON directly from the DB for the given site
    global $wpdb;
    $site_id = null;
    if (is_object($website) && isset($website->id)) {
        $site_id = $website->id;
    } elseif (is_array($website) && isset($website['id'])) {
        $site_id = $website['id'];
    }
    $users = array();
    if ($site_id) {
        $table = $wpdb->prefix . 'mainwp_wp';
        $user_json = $wpdb->get_var($wpdb->prepare("SELECT users FROM $table WHERE id = %d", $site_id));
        if ($user_json) {
            $decoded = json_decode($user_json);
            if (is_array($decoded)) {
                $users = $decoded;
            }
        }
    }
    // Format output for both tokens
    $list = '';
    $table = '';
    if (!empty($users)) {
        $list = MainWP_Pro_Reports_All_Users::get_instance()->format_users_list($users);
        $table = MainWP_Pro_Reports_All_Users::get_instance()->format_users_table($users);
    } else {
        $list = 'No users found';
        $table = 'No users found';
    }
    // Provide token values in both body and header
    $parsed_other_tokens['other_tokens_data']['body']['[allusers]'] = $list;
    $parsed_other_tokens['other_tokens_data']['body']['[allusers.table]'] = $table;
    $parsed_other_tokens['other_tokens_data']['header']['[allusers]'] = $list;
    $parsed_other_tokens['other_tokens_data']['header']['[allusers.table]'] = $table;

    return $parsed_other_tokens;
}, 10, 3);