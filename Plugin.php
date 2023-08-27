<?php
if (!defined('__TYPECHO_ROOT_DIR__')) exit;
/**
 * GitHubImageCache_Plugin
 * 
 * @package GitHubImageCache_Plugin 
 * @author happen
 * @version 1.0.0
 * @link http://typecho.org
 */
class GitHubImageCache_Plugin implements Typecho_Plugin_Interface
{
    public static function activate()
    {
        Typecho_Plugin::factory('Widget_Abstract_Contents')->contentEx = array('GitHubImageCache_Plugin', 'filter');
    }

    public static function deactivate() {}

    public static function config(Typecho_Widget_Helper_Form $form) {}

    public static function personalConfig(Typecho_Widget_Helper_Form $form) {}

    public static function filter($content, $widget, $lastResult)
    {
        return preg_replace_callback('/https:\/\/raw\.githubusercontent\.com\/([^\/]+\/[^\/]+)\/[^\/]+\/(.+?\.(png|jpg|jpeg|gif))/i', array('GitHubImageCache_Plugin', 'replace'), $content);
    }

    public static function replace($matches)
    {
        $url = $matches[0];
        $filename = basename($url);
        $localPath = __TYPECHO_ROOT_DIR__ . '/usr/uploads/github_cache/' . $filename;

        if (!file_exists($localPath)) {
            $imageData = file_get_contents($url);
            if ($imageData) {
                file_put_contents($localPath, $imageData);
            }
        }

        $localUrl = Typecho_Common::url('usr/uploads/github_cache/' . $filename, Helper::options()->siteUrl);
        return $localUrl;
    }
}
