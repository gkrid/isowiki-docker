<?php
/** @var action_plugin_bez $this */

use \dokuwiki\plugin\bez;

if ($this->model->get_level() < BEZ_AUTH_USER) {
    throw new bez\meta\PermissionDeniedException();
}

define('BEZ_THREAD_FILTERS_COOKIE_NAME', 'plugin__bez_thread_filters_' . $this->tpl->action());

if (count($_POST) > 0) {
    $raw_filters = $_POST;
} elseif (empty($this->params) && isset($_COOKIE[BEZ_THREAD_FILTERS_COOKIE_NAME])) {
	$raw_filters = json_decode($_COOKIE[BEZ_THREAD_FILTERS_COOKIE_NAME], true);
}

if (isset($raw_filters)) {
    //save filters
    setcookie(BEZ_THREAD_FILTERS_COOKIE_NAME, json_encode($raw_filters));

    $filters = array_filter($raw_filters, function($v) {
       return $v !== '-all' && $v !== '';
    });

    if (empty($filters)) {
        $filters['year'] = '-all';
    }

	header('Location: '.$this->url($this->get_action(), $filters));
} else {
    $filters = $this->params;
}

$this->tpl->set_values($filters);

$years = $this->model->threadFactory->get_years_scope();

//some filters are just copied
$db_filters = array_filter($filters, function ($k) {
    return in_array($k, array('original_poster', 'state', 'label_id', 'coordinator'));
}, ARRAY_FILTER_USE_KEY);

//-none filters become empty filters
$db_filters = array_map(function($v) {
    if ($v === '-none') {
        return '';
    }
    return $v;
}, $db_filters);

//don't show rejecetd by default
if (!isset($db_filters['state'])) {
    $db_filters['state'] = array('!=', 'rejected');
}

if (isset($filters['year']) && $filters['year'] !== '-all') {
    $year = $filters['year'];

    $start_day = "$year-01-01";
    $end_day = "$year-12-31";

    $db_filters['create_date'] = array('BETWEEN', array($start_day, $end_day), array('date'));
}

if (isset($filters['coordinator']) &&
        substr($filters['coordinator'], 0, 1) === '@') {
    $group = substr($filters['coordinator'], 1);
    $db_filters['coordinator'] = array('OR', $this->model->userFactory->users_of_group($group));
}

if (isset($filters['original_poster']) &&
    substr($filters['original_poster'], 0, 1) === '@') {
    $group = substr($filters['original_poster'], 1);
    $db_filters['original_poster'] = array('OR', $this->model->userFactory->users_of_group($group));
}

if (isset($filters['title'])) {
    $title = preg_replace('/\s/', '%', $filters['title']);
    $db_filters['title'] = array('LIKE', "%$title%");
}

if (isset($filters['has_causes'])) {
    $db_filters['cause_count'] = array('>', 0);
}

if (isset($filters['has_risks'])) {
    $db_filters['risk_count'] = array('>', 0);
}

if (isset($filters['has_opportunities'])) {
    $db_filters['opportunity_count'] = array('>', 0);
}



$orderby = array('sort', 'priority DESC', 'create_date DESC');
if (isset($filters['sort_open']) && $filters['sort_open'] == 'on') {
    $orderby = 'id DESC';
}

if ($this->get_action() == 'threads') {
    $db_filters['type'] = 'issue';
} else {
    $db_filters['type'] = 'project';
}

$threads = $this->model->threadFactory->get_all($db_filters, $orderby);

$this->tpl->set('labels', $this->model->labelFactory->get_all());
$this->tpl->set('threads', $threads);
$this->tpl->set('years', $years);
