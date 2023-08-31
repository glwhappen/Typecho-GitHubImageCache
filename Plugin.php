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

        // 如果本地已经有这张图片，直接返回本地URL
        if (file_exists($localPath)) {
            return Typecho_Common::url('usr/uploads/github_cache/' . $filename, Helper::options()->siteUrl);
        }

        // 使用cURL异步获取图片
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 3); // 设置超时时间为3秒
        $imageData = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        // 如果获取成功，并且HTTP状态码为200，保存图片到本地
        if ($imageData && $httpCode == 200) {
            file_put_contents($localPath, $imageData);
            return Typecho_Common::url('usr/uploads/github_cache/' . $filename, Helper::options()->siteUrl);
        }

        // 如果获取失败，返回原始URL
        return $url;
    }
}
