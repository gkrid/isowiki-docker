<?php
/**
 * DokuWiki Plugin structnotification (Action Component)
 *
 * @license GPL 2 http://www.gnu.org/licenses/gpl-2.0.html
 * @author  Szymon Olewniczak <it@rid.pl>
 */

// must be run within Dokuwiki
use dokuwiki\plugin\struct\meta\Search;
use dokuwiki\plugin\struct\meta\Value;

if (!defined('DOKU_INC')) {
    die();
}

class action_plugin_structnotification_notification extends DokuWiki_Action_Plugin
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
        $controller->register_hook('PLUGIN_NOTIFICATION_REGISTER_SOURCE', 'AFTER', $this, 'add_notifications_source');
        $controller->register_hook('PLUGIN_NOTIFICATION_GATHER', 'AFTER', $this, 'add_notifications');
        $controller->register_hook('PLUGIN_NOTIFICATION_CACHE_DEPENDENCIES', 'AFTER', $this, 'add_notification_cache_dependencies');


    }

    public function add_notifications_source(Doku_Event $event)
    {
        $event->data[] = 'structnotification';
    }

    public function add_notification_cache_dependencies(Doku_Event $event)
    {
        if (!in_array('structnotification', $event->data['plugins'])) return;

        try {
            /** @var \helper_plugin_structnotification_db $db_helper */
            $db_helper = plugin_load('helper', 'structnotification_db');
            $sqlite = $db_helper->getDB();
            $event->data['dependencies'][] = $sqlite->getAdapter()->getDbFile();
        } catch (Exception $e) {
            msg($e->getMessage(), -1);
            return;
        }
    }

    protected function getValueByLabel($values, $label)
    {
        /* @var Value $value */
        foreach ($values as $value) {
            $colLabel = $value->getColumn()->getLabel();
            if ($colLabel == $label) {
                return $value->getRawValue();
            }
        }
        //nothing found
        throw new Exception("column: $label not found in values");
    }


    public function add_notifications(Doku_Event $event)
    {
        if (!in_array('structnotification', $event->data['plugins'])) return;

        try {
            /** @var \helper_plugin_structnotification_db$db_helper */
            $db_helper = plugin_load('helper', 'structnotification_db');
            $sqlite = $db_helper->getDB();
        } catch (Exception $e) {
            msg($e->getMessage(), -1);
            return;
        }

        $user = $event->data['user'];

        $q = 'SELECT * FROM predicate';
        $res = $sqlite->query($q);

        $predicates = $sqlite->res2arr($res);

        foreach ($predicates as $predicate) {
            $schema = $predicate['schema'];
            $field = $predicate['field'];
            $operator = $predicate['operator'];
            $days = $predicate['days'];
            $users_and_groups = $predicate['users_and_groups'];
            $message = $predicate['message'];

             try {
                $search = new Search();
                $search->addSchema($schema);
                $search->addColumn('*');
                $result = $search->execute();
                $result_pids = $search->getPids();

                /* @var Value[] $row */
                for ($i=0; $i<count($result); $i++) {
                    $values = $result[$i];
                    $pid = $result_pids[$i];

                    $users_set = $this->users_set($users_and_groups, $values);
                    if (!isset($users_set[$user])) continue;

                    $rawDate = $this->getValueByLabel($values, $field);
                    if ($this->predicateTrue($rawDate, $operator, $days)) {
                        $message_with_replacements = $this->replacePlaceholders($message, $values);
                        $message_with_replacements_html = p_render('xhtml',
                            p_get_instructions($message_with_replacements), $info);
                        $event->data['notifications'][] = [
                            'plugin' => 'structnotification',
                            'id' => $predicate['id'] . ':'. $schema . ':' . $pid . ':'  . $rawDate,
                            'full' => $message_with_replacements_html,
                            'brief' => $message_with_replacements_html,
                            'timestamp' => (int) strtotime($rawDate)
                        ];
                    }
                }
            } catch (Exception $e) {
                msg($e->getMessage(), -1);
                return;
            }
        }
    }

    /**
     * @return array
     */
    protected function users_set($user_and_groups, $values) {
        /** @var DokuWiki_Auth_Plugin $auth */
        global $auth;

        //make substitutions
        $user_and_groups = preg_replace_callback(
            '/@@(.*?)@@/',
            function ($matches) use ($values) {
                list($schema, $field) = explode('.', trim($matches[1]));
                if (!$field) return '';
                /* @var Value $value */
                foreach ($values as $value) {
                    $column = $value->getColumn();
                    $colLabel = $column->getLabel();
                    $type = $column->getType();
                    if ($colLabel == $field) {
                        if (class_exists('\dokuwiki\plugin\structgroup\types\Group') &&
                            $type instanceof \dokuwiki\plugin\structgroup\types\Group) {
                            if ($column->isMulti()) {
                                return implode(',', array_map(function ($rawValue) {
                                    return '@' . $rawValue;
                                }, $value->getRawValue()));
                            } else {
                                return '@' . $value->getRawValue();
                            }
                        }
                        if ($column->isMulti()) {
                            return implode(',', $value->getRawValue());
                        } else {
                            return $value->getRawValue();
                        }
                    }
                }
                return '';
            },
            $user_and_groups
        );

        $user_and_groups_set = array_map('trim', explode(',', $user_and_groups));
        $users = [];
        $groups = [];
        foreach ($user_and_groups_set as $user_or_group) {
            if ($user_or_group[0] == '@') {
                $groups[] = substr($user_or_group, 1);
            } else {
                $users[] = $user_or_group;
            }
        }
        $set = [];

        $all_users = $auth->retrieveUsers();
        foreach ($all_users as $user => $info) {
            if (in_array($user, $users)) {
                $set[$user] = $info;
            } elseif (array_intersect($groups, $info['grps'])) {
                $set[$user] = $info;
            }
        }

        return $set;
    }

    protected function predicateTrue($date, $operator, $days) {
        $date = date('Y-m-d', strtotime($date));

        switch ($operator) {
            case 'before':
                $days = date('Y-m-d', strtotime("+$days days"));
                return $days >= $date;
            case 'after':
                $days = date('Y-m-d', strtotime("-$days days"));
                return $date <= $days;
            default:
                return false;
        }
    }

    protected function replacePlaceholders($message, $values) {
        $patterns = [];
        $replacements = [];
        /* @var Value $value */
        foreach ($values as $value) {
            $schema = $value->getColumn()->getTable();
            $label = $value->getColumn()->getLabel();
            $patterns[] = "/@@$schema.$label@@/";
            $replacements[] = $value->getDisplayValue();
        }

        return preg_replace($patterns, $replacements, $message);
    }

}

