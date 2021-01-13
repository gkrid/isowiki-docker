<?php
/** @var action_plugin_bez $this */

use \dokuwiki\plugin\bez;

if ($this->model->get_level() < BEZ_AUTH_USER) {
    throw new bez\meta\PermissionDeniedException();
}

$period = NULL;
if(count($_POST) > 0 && ($_POST['from'] != '' || $_POST['to'] != '')) {
    $from = new DateTime($_POST['from']);
    $to = new DateTime($_POST['to']);

    $this->tpl->set_values(array(
        'from' => $from->format('Y-m-d'),
        'to' => $to->format('Y-m-d')));

    $to->modify('+1 day');//add one day extra
    $period = new DatePeriod($from, new DateInterval('P1D'), $to);
}

$this->tpl->set('thread_involvement', $this->model->threadFactory->users_involvement($period));
$this->tpl->set('task_involvement', $this->model->taskFactory->users_involvement($period));
$this->tpl->set('kpi', $this->model->threadFactory->kpi($period));
$this->tpl->set('bez_activity', $this->model->threadFactory->bez_activity($period));