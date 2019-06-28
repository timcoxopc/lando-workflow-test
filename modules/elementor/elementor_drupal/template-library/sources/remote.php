<?php
namespace Drupal\elementor;

use Elementor\Api;
use Elementor\Plugin;
use Elementor\TemplateLibrary\Source_Base;

use Drupal\elementor\ElementorPlugin;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

/**
 * Elementor template library remote source.
 *
 * Elementor template library remote source handler class is responsible for
 * handling remote templates from Elementor.com servers.
 *
 * @since 1.0.0
 */
class Source_Remote extends Source_Base
{

    /**
     * Get remote template ID.
     *
     * Retrieve the remote template ID.
     *
     * @since 1.0.0
     * @access public
     *
     * @return string The remote template ID.
     */
    public function get_id()
    {
        return 'remote';
    }

    /**
     * Get remote template title.
     *
     * Retrieve the remote template title.
     *
     * @since 1.0.0
     * @access public
     *
     * @return string The remote template title.
     */
    public function get_title()
    {
        return ___elementor_adapter('Remote', 'elementor');
    }

    /**
     * Register remote template data.
     *
     * Used to register custom template data like a post type, a taxonomy or any
     * other data.
     *
     * @since 1.0.0
     * @access public
     */
    public function register_data()
    {}

    /**
     * Get remote templates.
     *
     * Retrieve remote templates from Elementor.com servers.
     *
     * @since 1.0.0
     * @access public
     *
     * @param array $args Optional. Filter templates list based on a set of
     *                    arguments. Default is an empty array.
     *
     * @return array Remote templates.
     */
    public function get_items($args = [])
    {
        $library_data = ElementorPlugin::$instance->sdk->get_remote_templates('remote');
        if (!$library_data) {
            $library_data = Api::get_library_data();
            ElementorPlugin::$instance->sdk->save_remote_templates('remote', $library_data);
        }

        $templates = [];

        if (!empty($library_data['templates'])) {
            foreach ($library_data['templates'] as $template_data) {
                $templates[] = $this->get_item($template_data);
            }
        }

        if (!empty($args)) {
            $templates = wp_list_filter($templates, $args);
        }

        return $templates;
    }

    /**
     * Get remote template.
     *
     * Retrieve a single remote template from Elementor.com servers.
     *
     * @since 1.0.0
     * @access public
     *
     * @param array $template_data Remote template data.
     *
     * @return array Remote template.
     */
    public function get_item($template_data)
    {
        $favorite_templates = $this->get_user_meta_elementor_adapter('favorites');

        return [
            'template_id' => $template_data['id'],
            'source' => $this->get_id(),
            'type' => $template_data['type'],
            'subtype' => $template_data['subtype'],
            'title' => $template_data['title'],
            'thumbnail' => $template_data['thumbnail'],
            'date' => $template_data['tmpl_created'],
            'author' => $template_data['author'],
            'tags' => json_decode($template_data['tags']),
            'isPro' => ('1' === $template_data['is_pro']),
            'popularityIndex' => (int) $template_data['popularity_index'],
            'trendIndex' => (int) $template_data['trend_index'],
            'hasPageSettings' => ('1' === $template_data['has_page_settings']),
            'url' => $template_data['url'],
            'favorite' => !empty($favorite_templates[$template_data['id']]),
        ];
    }

    /**
     * Save remote template.
     *
     * Remote template from Elementor.com servers cannot be saved on the
     * database as they are retrieved from remote servers.
     *
     * @since 1.0.0
     * @access public
     *
     * @param array $template_data Remote template data.
     *
     * @return bool Return false.
     */
    public function save_item($template_data)
    {
        return false;
    }

    /**
     * Update remote template.
     *
     * Remote template from Elementor.com servers cannot be updated on the
     * database as they are retrieved from remote servers.
     *
     * @since 1.0.0
     * @access public
     *
     * @param array $new_data New template data.
     *
     * @return bool Return false.
     */
    public function update_item($new_data)
    {
        return false;
    }

    /**
     * Delete remote template.
     *
     * Remote template from Elementor.com servers cannot be deleted from the
     * database as they are retrieved from remote servers.
     *
     * @since 1.0.0
     * @access public
     *
     * @param int $template_id The template ID.
     *
     * @return bool Return false.
     */
    public function delete_template($template_id)
    {
        return false;
    }

    /**
     * Export remote template.
     *
     * Remote template from Elementor.com servers cannot be exported from the
     * database as they are retrieved from remote servers.
     *
     * @since 1.0.0
     * @access public
     *
     * @param int $template_id The template ID.
     *
     * @return bool Return false.
     */
    public function export_template($template_id)
    {
        return false;
    }
    
    /**
     * Get remote template data.
     *
     * Retrieve the data of a single remote template from Elementor.com servers.
     *
     * @since 1.5.0
     * @access public
     *
     * @param array  $args    Custom template arguments.
     * @param string $context Optional. The context. Default is `display`.
     *
     * @return array Remote Template data.
     */
    public function get_data(array $args, $context = 'display')
    {
        $data = Api::get_template_content($args['template_id']);

        if (is_wp_error_elementor_adapter($data)) {
            return $data;
        }

        $data['content'] = $this->replace_elements_ids($data['content']);
    	$data['content'] = $this->process_export_import_content( $data['content'], 'on_import' );

        $post_id = $_POST['editor_post_id'];
        $document = Plugin::$instance->documents->get($post_id);

        if ($document) {
            try {
                $data['content'] = $document->get_elements_raw_data($data['content'], true);
            } catch (Exception $e) {
                // $data['content'] = ;
            }
        }

        return $data;
    }
}
