<?php

/*
Plugin Name: ESO Hub
Plugin URI: https://github.com/PathfinderMediaGroup/eso-hub-wordpress
Description: Embed tooltips of the game The Elder Scrolls Online into your pages and posts.
Version: 1.1.7
Author: Woeler
Author URI: https://www.pathfindermediagroup.com
License: GPL-3
*/

defined('ABSPATH') || exit;

add_action('plugins_loaded', 'EsoHub::setup');

final class EsoHub
{
    public function __construct()
    {
    }

    public static function setup()
    {
        $esoHub = new EsoHub();
        add_action('wp_enqueue_scripts', [$esoHub, 'addStyle']);
        add_action('wp_enqueue_scripts', [$esoHub, 'addScript']);
        add_shortcode('esohub_skillbar', [$esoHub, 'skillbar']);
    }

    public function addStyle()
    {
        wp_enqueue_style('ESO-Hub', 'https://eso-hub.com/css/external/tooltips.css');
    }

    public function addScript()
    {
        wp_enqueue_script('ESO-Hub', 'https://eso-hub.com/js/external/tooltips.js');
    }

    public function skillbar($atts)
    {
        $cache = get_transient(md5('esohub_skillbar_' . serialize($atts)));
        if (!is_preview() && $cache) {
            return $cache;
        }

        $baseUri = 'https://eso-hub.com/storage';
        $return = '<div class="eso-hub-skillbar">';

        foreach ($atts as $key => $skillUrl) {
            $skillUrl = str_replace('www.', '', $skillUrl);
            if (strpos($skillUrl, 'https://eso-hub.com/') !== 0) {
                continue;
            }

            if (strpos($skillUrl, '/companions/') !== false) {
                // Is companion skill
                $image = 'companions/'.str_replace([
                        'https://eso-hub.com/en/companions/skills/',
                        'https://eso-hub.com/de/companions/skills/',
                        'https://eso-hub.com/fr/companions/skills/'
                    ], '', $skillUrl) . '.png';
            } else {
                $image = str_replace([
                        'https://eso-hub.com/en/skills/',
                        'https://eso-hub.com/de/skills/',
                        'https://eso-hub.com/fr/skills/'
                    ], '', $skillUrl) . '.png';
            }

            $return .= '<a href="' . $skillUrl . '" target="_blank" rel="noopener">';
            $return .= '<img class="eso-hub-skill-img';
            if (strpos($key, 'passive_') === 0) {
                $return .= ' passive-skill';
            }
            $return .= '" src="' . $baseUri . '/skills/' . $image . '" alt="ESO-Hub Skill icon"';
            if (strpos($key, 'ultimate_') === 0) {
                $return .= ' style="margin-left: 20px"';
            }
            $return .= '/>';
            $return .= '</a>';
        }

        $return .= '</div>';

        if (!is_preview()) {
            set_transient(md5('esohub_skillbar_' . serialize($atts)), $return, 3600);
        }

        return $return;
    }
}
