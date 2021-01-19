<?php /* @var \dokuwiki\plugin\bez\meta\Tpl $tpl */ ?>

<div id="z<?php echo $tpl->get('task')->id ?>"
	class="bez_task
    <?php
        if($tpl->get('task')->state == 'opened') {
            echo 'priority_' . $tpl->get('task')->priority;
        }
    ?>">

<div class="timebox">
    <span>
        <strong><?php echo $tpl->getLang('open') ?>:</strong>
        <?php echo $tpl->date($tpl->get('task')->create_date) ?>
    </span>

	<?php if ($tpl->get('task')->state != 'opened'): ?>

        <span>
            <strong><?php echo $tpl->getLang('task_' . $tpl->get('task')->state) ?>:</strong>
            <?php echo $tpl->date($tpl->get('task')->close_date) ?>
        </span>

		<span>
			<strong><?php echo $tpl->getLang('report_priority') ?>: </strong>
            <?php echo $tpl->date_diff_days($tpl->get('task')->create_date, $tpl->get('task')->close_date, '%a'); ?>
		</span>
	<?php endif ?>
</div>

<h2>
	<a href="<?php echo $tpl->url('task', 'tid', $tpl->get('task')->id) ?>">
		#z<?php echo $tpl->get('task')->id ?>
	</a>
    <?php if ($tpl->get('task')->thread_id == '' || $tpl->get('task')->thread->type != 'project'): ?>
	    <?php echo lcfirst($tpl->getLang('task_type_' . $tpl->get('task')->type)) ?>
    <?php endif ?>
	(<?php echo lcfirst($tpl->getLang('task_' . $tpl->get('task')->state)) ?>)

    <?php if ($tpl->get('task')->private == '1'): ?>
        <?php echo inlineSVG(DOKU_PLUGIN . 'bez/images/lock.svg') ?>
    <?php endif ?>
</h2>

<table class="data">
<tr>
    <th><?php echo $tpl->getLang('reporter') ?>:</th>
    <td>
        <?php echo $tpl->user_name($tpl->get('task')->original_poster) ?>
    </td>

    <th><?php echo $tpl->getLang('executor') ?>:</th>
    <td><?php echo $tpl->user_name($tpl->get('task')->assignee) ?></td>
</tr>

<tr>
    <th style="white-space: nowrap;"><?php echo $tpl->getLang('plan_date') ?>:</th>
    <td>
        <?php echo $tpl->get('task')->plan_date ?><?php if ($tpl->get('task')->all_day_event == '0'): ?>,
            <?php echo $tpl->get('task')->start_time ?> - <?php echo $tpl->get('task')->finish_time ?>
        <?php endif ?>
    </td>

    <th><?php echo $tpl->getLang('task_type') ?>:</th>
    <td>
    <?php if ($tpl->get('task')->task_program_id == ''): ?>
        ---
    <?php else: ?>
        <?php echo $tpl->get('task')->task_program_name ?>
    <?php endif ?>
    </td>
</tr>

<tr>
    <th><?php echo $tpl->getLang('cost') ?>:</th>
    <td colspan="3">
    <?php if ($tpl->get('task')->cost == ''): ?>
        ---
    <?php else: ?>
        <?php echo $tpl->get('task')->cost ?>
    <?php endif ?>
    </td>
</tr>

</table>

<?php echo $tpl->get('task')->content_html ?>
<?php if (!$tpl->get('no_actions')): ?>
    <?php if ($tpl->action() == 'thread'): ?>
        <a style="display:block; position: absolute"
           href="<?php echo $tpl->url('task', 'tid', $tpl->get('task')->id) ?>">
            <?php echo $tpl->getLang('comments') ?>:
            <?php echo $tpl->factory('task_comment')->count(array('task_id' => $tpl->get('task')->id)) ?>
        </a>
    <?php endif ?>

    <div class="bez_buttons">
        <?php if ($tpl->get('task')->acl_of('thread_id') >= BEZ_PERMISSION_CHANGE): ?>
            <?php if ($tpl->get('task')->thread_id == ''): ?>
                <form   id="plugin__bez_task_pin_form"
                        action="<?php echo $tpl->url('task', 'tid', $tpl->get('task')->id, 'action', 'pin') ?>"
                        method="post">
                    <label>
                        <?php echo $tpl->getLang('thread_id') ?>:
                        <input name="thread_id" type="number" style="width: 50px"
                               value="<?php echo $tpl->value('thread_id') ?>" />
                        <button style="display:none">
                            <?php echo $tpl->getLang('pin_button') ?>
                        </button>
                    </label>
                    <button id="plugin__bez_pin_to_the_issue" class="bds_inline_button"
                       href="<?php echo $tpl->url('task', 'tid', $tpl->get('task')->id) ?>#zk_">
                        <span class="bez_awesome">&#xf0c6;</span>
                        <?php echo $tpl->getLang('pin_to_the_issue') ?>
                    </button>
                </form>
            <?php else: ?>
                <a class="bds_inline_button"
                   href="<?php echo $tpl->url('task', 'tid', $tpl->get('task')->id, 'action', 'unpin') ?>"
                   onclick="return confirm('<?php echo $tpl->getLang('confirm_unpin_task') ?>')">
                    <span class="bez_awesome">&#xf0c6;</span>
                    <?php echo $tpl->getLang('unpin_from_the_issue') ?>
                </a>
            <?php endif ?>
        <?php endif ?>

        <?php if ($tpl->get('task')->acl_of('state') >= BEZ_PERMISSION_CHANGE): ?>
            <a class="bds_inline_button"
               id="plugin__bez_do_task_button"
               href="<?php echo $tpl->url('task', 'tid', $tpl->get('task')->id) ?>#zk_">
                <?php if ($tpl->get('task')->state == 'opened'): ?>
                    ↬ <?php echo $tpl->getLang('js')['do_task'] ?>
                <?php else: ?>
                    ↻ <?php echo $tpl->getLang('js')['reopen_task'] ?>
                <?php endif?>
            </a>
        <?php endif ?>

        <?php if (count($tpl->get('task')->changable_fields(
                array('content', 'plan_date', 'all_day_event', 'start_time', 'finish_time', 'task_program_id', 'cost')
            )) > 0): ?>
                <a class="bds_inline_button"
                    href="<?php
                        if ($tpl->action() == 'thread') {
                            echo $tpl->url('thread', 'id', $tpl->get('thread')->id, 'tid', $tpl->get('task')->id, 'action', 'task_edit');
                        } else {
                            echo $tpl->url('task', 'tid', $tpl->get('task')->id, 'action', 'task_edit');
                        }
                    ?>#z_">
                    ✎ <?php echo $tpl->getLang('edit') ?>
                </a>
        <?php endif ?>

        <?php if ($tpl->get('task')->acl_of('id') >= BEZ_PERMISSION_DELETE): ?>
            <a class="bds_inline_button bez_commcause_delete_prompt"
               href="<?php
               if ($tpl->action() == 'thread') {
                   echo $tpl->url('thread', 'id', $tpl->get('thread')->id, 'tid', $tpl->get('task')->id, 'action', 'task_delete');
               } else {
                   echo $tpl->url('task', 'tid', $tpl->get('task')->id, 'action', 'task_delete');
               }
               ?>#z_">
                ✕ <?php echo $tpl->getLang('delete') ?>
            </a>
        <?php endif ?>

        <a class="bds_inline_button" href="
        <?php echo $tpl->mailto($tpl->user_email($tpl->get('task')->assignee),
        '#z'.$tpl->get('task')->id,
        $tpl->url('task', 'tid', $tpl->get('task')->id)) ?>">
            ✉ <?php echo $tpl->getLang('send_mail') ?>
        </a>

        <?php if ($tpl->get('task')->task_program_id != '' &&
                  $tpl->factory('task')->permission() >= BEZ_TABLE_PERMISSION_INSERT): ?>
            <a class="bds_inline_button"
                    href="<?php echo $tpl->url('task_form', 'duplicate', $tpl->get('task')->id, 'task_program_id', $tpl->get('task')->task_program_id) ?>">
                    ⇲ <?php echo $tpl->getLang('duplicate') ?>
            </a>
        <?php endif ?>
	</div>
<?php endif ?>

</div>
