<?php

/**
 * @file
 * Contains \Drupal\elementor\Drupal_Revisions_Manager.
 */

namespace Drupal\elementor;

use Elementor\Core\Settings\Manager;
use Elementor\Modules\History\Revisions_Manager;
use Elementor\Utils;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

class Drupal_Revisions_Manager extends Revisions_Manager
{

    public function __construct()
    {
        self::register_actions();
    }

    public static function get_revisions($id = 1, $query_args = [], $parse_result = true)
    {
        $result = ElementorPlugin::$instance->sdk->get_revisions($id);
        $revisions = [];

        foreach ($result as $revision) {
            date_default_timezone_set('UTC');
            $date = date('M j @ H:i', $revision->timestamp);
            $human_time = human_time_diff_elementor_adapter($revision->timestamp);

            $type = 'revision';

            $revisions[] = [
                'id' => $revision->id,
                'author' => $revision->author,
                'timestamp' => strtotime($date),
                'date' => sprintf(
                    ___elementor_adapter('%1$s ago (%2$s)', 'elementor'),
                    $human_time,
                    $date
                ),
                'type' => $type,
                'gravatar' => null,
            ];
        }

        return $revisions;
    }

    public static function get_revisions_ids($id = 1, $query_args = [], $parse_result = true)
    {
        $revisions = ElementorPlugin::$instance->sdk->get_revisions_ids($id);
        return $revisions;
    }

    public static function on_revision_data_request()
    {
        $revision_data = ElementorPlugin::$instance->sdk->get_revision_data($_POST['id']);
        return wp_send_json_success_elementor_adapter($revision_data);
    }

    public static function on_delete_revision_request()
    {
        $result = ElementorPlugin::$instance->sdk->delete_revision($_POST['id']);
        return wp_send_json_success_elementor_adapter();
    }

    public static function on_ajax_save_builder_data($return_data, $document)
    {
        $id = $_POST['editor_post_id'];

        $latest_revisions = self::get_revisions($id);

        $all_revision_ids = self::get_revisions_ids($id);

        // Send revisions data only if has revisions.
        if (!empty($latest_revisions)) {
            $current_revision_id = $id;

            $return_data = array_replace_recursive($return_data, [
                'config' => [
                    'current_revision_id' => $current_revision_id,
                ],
                'latest_revisions' => $latest_revisions,
                'revisions_ids' => $all_revision_ids,
            ]);
        }

        return $return_data;
    }

    public static function db_before_save($status, $has_changes)
    {

    }

    public static function editor_settings($settings, $id)
    {
        $settings = array_replace_recursive($settings, [
            'revisions' => self::get_revisions(),
            'revisions_enabled' => true,
            'current_revision_id' => $id,
            'i18n' => [
                'edit_draft' => ___elementor_adapter('Edit Draft', 'elementor'),
                'edit_published' => ___elementor_adapter('Edit Published', 'elementor'),
                'no_revisions_1' => ___elementor_adapter('Revision history lets you save your previous versions of your work, and restore them any time.', 'elementor'),
                'no_revisions_2' => ___elementor_adapter('Start designing your page and you\'ll be able to see the entire revision history here.', 'elementor'),
                'current' => ___elementor_adapter('Current Version', 'elementor'),
                'restore' => ___elementor_adapter('Restore', 'elementor'),
                'restore_auto_saved_data' => ___elementor_adapter('Restore Auto Saved Data', 'elementor'),
                'restore_auto_saved_data_message' => ___elementor_adapter('There is an autosave of this post that is more recent than the version below. You can restore the saved data fron the Revisions panel', 'elementor'),
                'revision' => ___elementor_adapter('Revision', 'elementor'),
                'revision_history' => ___elementor_adapter('Revision History', 'elementor'),
                'revisions_disabled_1' => ___elementor_adapter('It looks like the post revision feature is unavailable in your website.', 'elementor'),
                'revisions_disabled_2' => sprintf(
                    /* translators: %s: Codex URL */
                    ___elementor_adapter('Learn more about <a target="_blank" href="%s">WordPress revisions</a>', 'elementor'),
                    'https://codex.wordpress.org/Revisions#Revision_Options'
                ),
            ],
        ]);

        return $settings;
    }

    private static function register_actions()
    {
        remove_action_elementor_adapter('wp_restore_post_revision', [__CLASS__, 'restore_revision'], 10, 2);
        remove_action_elementor_adapter('init', [__CLASS__, 'add_revision_support_for_all_post_types'], 9999);
        remove_filter_elementor_adapter('elementor/editor/localize_settings', [__CLASS__, 'editor_settings'], 10, 2);
        remove_action_elementor_adapter('elementor/db/before_save', [__CLASS__, 'db_before_save'], 10, 2);
        remove_action_elementor_adapter('_wp_put_post_revision', [__CLASS__, 'save_revision']);
        remove_action_elementor_adapter('wp_creating_autosave', [__CLASS__, 'update_autosave']);

        // Hack to avoid delete the auto-save revision in WP editor.
        remove_action_elementor_adapter('edit_post_content', [__CLASS__, 'avoid_delete_auto_save'], 10, 2);
        remove_action_elementor_adapter('edit_form_after_title', [__CLASS__, 'remove_temp_post_content']);

        if (Utils::is_ajax()) {
            remove_filter_elementor_adapter('elementor/documents/ajax_save/return_data', [__CLASS__, 'on_ajax_save_builder_data'], 10, 2);
            remove_action_elementor_adapter('wp_ajax_elementor_get_revision_data', [__CLASS__, 'on_revision_data_request']);
            remove_action_elementor_adapter('wp_ajax_elementor_delete_revision', [__CLASS__, 'on_delete_revision_request']);
        }

        add_filter_elementor_adapter('elementor/editor/localize_settings', [__CLASS__, 'editor_settings'], 10, 2);
        add_action_elementor_adapter('elementor/db/before_save', [__CLASS__, 'db_before_save'], 10, 2);

        if (Utils::is_ajax()) {
            add_filter_elementor_adapter('elementor/documents/ajax_save/return_data', [__CLASS__, 'on_ajax_save_builder_data'], 10, 2);
            add_action_elementor_adapter('wp_ajax_elementor_get_revision_data', [__CLASS__, 'on_revision_data_request']);
            add_action_elementor_adapter('wp_ajax_elementor_delete_revision', [__CLASS__, 'on_delete_revision_request']);
        }
    }

}
