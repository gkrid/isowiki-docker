<?php
/**
 * DokuWiki Plugin telleveryone (Action Component)
 *
 * @license GPL 2 http://www.gnu.org/licenses/gpl-2.0.html
 * @author  Szymon Olewniczak <it@rid.pl>
 */

// must be run within Dokuwiki
if (!defined('DOKU_INC')) {
    die();
}

class action_plugin_telleveryone_notification extends DokuWiki_Action_Plugin
{

    /**
     * Registers a callback function for a given event
     *
     * @param Doku_Event_Handler $controller DokuWiki's event controller object
     *
     * @return void
     */
    public function register(Doku_Event_Handler $controller)
    {
        $controller->register_hook('PLUGIN_NOTIFICATION_REGISTER_SOURCE', 'AFTER', $this, 'handle_plugin_notification_register_source');
        $controller->register_hook('PLUGIN_NOTIFICATION_CACHE_DEPENDENCIES', 'AFTER', $this, 'handle_plugin_notification_cache_dependencies');
        $controller->register_hook('PLUGIN_NOTIFICATION_GATHER', 'AFTER', $this, 'handle_plugin_notification_gather');

    }

    /**
     * [Custom event handler which performs action]
     *
     * Called for event:
     *
     * @param Doku_Event $event  event object by reference
     * @param mixed      $param  [the parameters passed as fifth argument to register_hook() when this
     *                           handler was registered]
     *
     * @return void
     */
    public function handle_plugin_notification_register_source(Doku_Event $event, $param)
    {
        $event->data[] = 'telleveryone';
    }

    public function handle_plugin_notification_cache_dependencies(Doku_Event $event, $param)
    {
        if (!in_array('telleveryone', $event->data['plugins'])) return;

        //when we use REMOTE API - we cannot use cache
        if ($this->getConf('remote')) {
            $event->data['_nocache'] = true;
            return;
        }

        try {
            /** @var \helper_plugin_telleveryone_db $db_helper */
            $db_helper = plugin_load('helper', 'telleveryone_db');
            $sqlite = $db_helper->getDB();
            $event->data['dependencies'][] = $sqlite->getAdapter()->getDbFile();
        } catch (Exception $e) {
            msg($e->getMessage(), -1);
            return;
        }
    }

    public function handle_plugin_notification_gather(Doku_Event $event, $param)
    {
        if (!in_array('telleveryone', $event->data['plugins'])) return;

        try {
            /** @var \helper_plugin_telleveryone_db $db_helper */
            $db_helper = plugin_load('helper', 'telleveryone_db');
            $sqlite = $db_helper->getDB();
        } catch (Exception $e) {
            msg($e->getMessage(), -1);
            return;
        }

        $q = 'SELECT id, timestamp, message_html FROM log ORDER BY timestamp DESC LIMIT ?';
        $res = $sqlite->query($q, $this->getConf('limit'));

        $logs = $sqlite->res2arr($res);

        //load remote logs
        $remote_logs_sources = array_filter(explode("\n", $this->getConf('remote')));
        foreach ($remote_logs_sources as $source) {
            list($url, $token) = preg_split('/\s+/', trim($source), 2);
            if (empty($url)) continue;
            if (empty($token)) {
                msg('No token provided for "telleveryone" API: ' . $url, -1);
                continue;
            }
            $full_url = rtrim($url, '/');

            $query = http_build_query(['token' => $token, 'limit' => $this->getConf('limit')]);
            $full_url .= '/lib/plugins/telleveryone/api.php?' . $query;
            $result = file_get_contents($full_url);
            if (!$result) {
                msg('Cannot access "telleveryone" API for ' . $url, -1);
                continue;
            }
            $remote_logs = json_decode($result, true);
            $remote_logs = array_map(function ($log) use ($url) {
                $log['id'] = $url . ':' . $log['id'];
                return $log;
            }, $remote_logs);
            $logs = array_merge($logs, $remote_logs);
        }

        //sort by timestamp and remove to fit $conf['limit']
        $timestamps = array_map('strtotime', array_column($logs, 'timestamp'));
        array_multisort($timestamps, SORT_DESC, $logs);
        $logs = array_slice($logs, 0, $this->getConf('limit'));

        foreach ($logs as $log) {
            $id = $log['id'];
            $timestamp = strtotime($log['timestamp']);
            $message = $log['message_html'];

            $event->data['notifications'][] = [
                'plugin' => 'telleveryone',
                'id' => $id,
                'full' => $message,
                'brief' => $message,
                'timestamp' => $timestamp
            ];
        }
    }

}

