<?php

namespace Grav\Plugin;

use Grav\Common\Plugin;
use RocketTheme\Toolbox\Event\Event;

/**
 * Class TribunePlugin
 * @package Grav\Plugin
 */
class TribunePlugin extends Plugin {

    /**
     * @return array
     *
     * The getSubscribedEvents() gives the core a list of events
     *     that the plugin wants to listen to. The key of each
     *     array section is the event that the plugin listens to
     *     and the value (in the form of an array) contains the
     *     callable (or function) as well as the priority. The
     *     higher the number the higher the priority.
     */
    public static function getSubscribedEvents() {
        return [
            'onPluginsInitialized' => ['onPluginsInitialized', 0]
        ];
    }

    public function onPluginsInitialized() {
        // Don't proceed if we are in the admin plugin
        if ($this->isAdmin()) {
            return;
        }

        $uri = $this->grav['uri'];
        $page = $this->config->get('plugins.tribune.page');
        if ($page && $uri->path() == $page) {
            if ($uri->query('backend') === 'tsv') {
                $this->handlePost();
                $this->enable([
                    'onPageInitialized' => ['deliverTSV', 0],
                ]);
            } else {
                $this->enable([
                    'onPageContentRaw' => ['onPageContentRaw', 0],
                    'onAssetsInitialized' => ['onAssetsInitialized', 0]
                ]);
            }
        }
    }

    /**
     * Do some work for this event, full details of events can be found
     * on the learn site: http://learn.getgrav.org/plugins/event-hooks
     *
     * @param Event $e
     */
    public function onPageContentRaw(Event $e) {
        // Get a variable from the plugin configuration
        $text = <<<COIN
<form id="palmipede" accept-charset="UTF-8" enctype="application/x-www-form-urlencoded" autofocus="autofocus" class="palmipede">
    <input name="message" placeholder="message" spellcheck="true">
    <button type="submit">Post</button>
    </form>
<div id="tribune" class="tribune"></div>
COIN;

        // Get the current raw content
        $content = $e['page']->getRawContent();

        // Prepend the output with the custom text and set back on the page
        $e['page']->setRawContent($text . "\n\n" . $content);
    }

    public function onAssetsInitialized() {
        if ($this->config->get('plugins.tribune.style')) {
            $this->grav['assets']->addCss('plugin://tribune/tribune.css');
        }
        $this->grav['assets']->addJs('plugin://tribune/peg-0.10.0.js', null /* priority */, true /* pipeline */, 'defer');
        $this->grav['assets']->addJs('plugin://tribune/tribune.js', null /* priority */, true /* pipeline */, 'defer');
    }

    public function handlePost() {
        $message = mb_substr(filter_input(INPUT_POST, 'message', FILTER_UNSAFE_RAW, FILTER_FLAG_STRIP_LOW), 0, $this->config->get('plugins.tribune.maxMessageLength'));
        if (mb_strlen(trim($message)) > 0 && mb_detect_encoding($message, 'UTF-8', true)) {
            $info = trim(mb_substr(filter_input(INPUT_SERVER, 'HTTP_USER_AGENT', FILTER_SANITIZE_EMAIL), 0, $this->config->get('plugins.tribune.maxInfoLength')));
            $login = '';
            if (isset($this->grav['twig'])) {
                $user = $this->grav['user'];
                $login = trim(mb_substr($user->get('username', ''), 0, $this->config->get('plugins.tribune.maxLoginLength')));
            }
            if (!mb_detect_encoding($login, 'UTF-8', true)) {
                $login = "";
            }
            if (!mb_detect_encoding($info, 'UTF-8', true)) {
                $info = "relou";
            }
            if (mb_strlen($login) === 0 && mb_strlen($info) === 0) {
                $info = "coward";
            }
            $file = fopen(DATA_DIR . 'tribune.tsv', "c+");
            flock($file, LOCK_EX);
            $newPostId = 0;
            $newPosts = array();
            $maxLineLength = $this->config->get('plugins.tribune.maxLineLength');
            while (($post = fgetcsv($file, $maxLineLength, "\t")) !== FALSE) {
                $newPosts[] = $post;
                $newPostId = max($newPostId, $post[0]);
            }
            ++$newPostId;
            header('X-Post-Id: ' . $newPostId);
            $dateTime = date_create("now", timezone_open($this->config->get('plugins.tribune.timezone')));
            $time = date_format($dateTime, 'YmdHis');
            array_unshift($newPosts, array($newPostId, $time, $info, $login, $message));
            array_splice($newPosts, $this->config->get('plugins.tribune.maxPosts'));
            ftruncate($file, 0);
            fseek($file, 0);
            foreach ($newPosts as $post) {
                fputs($file, implode("\t", $post));
                fputs($file, "\n");
            }
            fclose($file);
        }
    }

    public function deliverTSV() {
        $lastId = filter_input(INPUT_POST, 'lastId', FILTER_VALIDATE_INT, array('options' => array('default' => 0)));
        header("Content-Type: text/tab-separated-values");
        $file = fopen(DATA_DIR . 'tribune.tsv', "r");
        $posts = array();
        $maxLineLength = $this->config->get('plugins.tribune.maxLineLength');
        while (($post = fgetcsv($file, $maxLineLength, "\t")) !== FALSE) {
            $posts[] = $post;
        }
        fclose($file);
        $outstream = fopen("php://output", 'w');
        foreach ($posts as $post) {
            if ($post[0] > $lastId) {
                fputs($outstream, implode("\t", $post));
                fputs($outstream, "\n");
            }
        }
        fclose($outstream);
        exit();
    }

}
