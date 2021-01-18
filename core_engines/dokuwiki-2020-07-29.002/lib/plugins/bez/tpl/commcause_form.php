<?php /* @var \dokuwiki\plugin\bez\meta\Tpl $tpl */ ?>
<?php $url = $tpl->url('thread', 'id', $tpl->get('thread')->id, 'action',
	$tpl->param('action') == 'commcause_edit' ? 'commcause_edit' : 'commcause_add',
'kid', $tpl->param('kid')) ?>
<a id="k_"></a>
<form class="bez_form_blank" action="<?php echo $url ?>" method="POST">
	<input type="hidden" name="id" value="">
	<div class="bez_comment bez_comment_form">
		<div class="bez_avatar">
			<img src="<?php echo DOKU_URL ?>lib/plugins/bez/images/avatar_default.png" />
		</div>
		<div class="bez_text_comment">
			<span class="bez_arrow-tip-container">
				<span class="bez_arrow-tip">
					<span class="bez_arrow-tip-grad"></span>
				</span>
			</span>
			<div class="commcause_content">
			<h2>
				<?php if ($tpl->get('thread_comment')->acl_of('type') >= BEZ_PERMISSION_CHANGE &&
                          $tpl->get('thread')->can_add_causes()): ?>
				<ul class="bez_tabs">
					<li
                        <?php if (  $tpl->value('type') == '' ||
                                    $tpl->value('type') == 'comment') echo 'class="active"' ?>
                        <?php if (  $tpl->param('kid') !== '' &&
                                    $tpl->get('thread_comment')->task_count > 0)
                                echo 'style="display:none;"';
                        ?>
                    >
                        <input style="display: none" type="radio" name="type" value="comment" <?php if($tpl->value('type') == '' || $tpl->value('type') === 'comment') echo 'checked="checked"' ?>>
                        <a href="#comment"><?php echo $tpl->getLang('comment_noun') ?></a>
                    </li>
					<li <?php if($tpl->value('type') === 'cause') echo 'class="active"' ?>>
                        <input style="display: none" type="radio" name="type" value="cause" <?php if($tpl->value('type') === 'cause') echo 'checked="checked"' ?>>
                        <a href="#cause"><?php echo $tpl->getLang('cause_noun') ?></a>
                    </li>
                    <li <?php if($tpl->value('type') === 'risk') echo 'class="active"' ?>>
                        <input style="display: none" type="radio" name="type" value="risk" <?php if($tpl->value('type') === 'risk') echo 'checked="checked"' ?>>
                        <a href="#cause"><?php echo $tpl->getLang('risk_noun') ?></a>
                    </li>
                    <li <?php if($tpl->value('type') === 'opportunity') echo 'class="active"' ?>>
                        <input style="display: none" type="radio" name="type" value="opportunity" <?php if($tpl->value('type') === 'opportunity') echo 'checked="checked"' ?>>
                        <a href="#cause"><?php echo $tpl->getLang('opportunity_noun') ?></a>
                    </li>
				</ul>
                <?php else: ?>
                <ul class="bez_tabs">
                    <li class="active">
                        <a href="#comment"><?php echo $tpl->getLang('comment_noun') ?></a>
                    </li>
                </ul>
				<?php endif ?>
			</h2>
			</div>
			<div class="bez_content">
                <div class="bez_toolbar" style="line-height:0"></div>
				<textarea name="content" class="bez_textarea_content" id="content1"><?php echo $tpl->value('content') ?></textarea>

                <div class="plugin__bez_form_buttons">

                <div class="plugin__bez_form_buttons_container">
                <?php if ($tpl->param('kid') != ''): ?>
                    <a href="<?php echo $tpl->url('thread', 'id', $tpl->get('thread')->id) ?><?php if ($tpl->param('kid') != '') echo '#k'.$tpl->param('kid') ?>"
                       class="plugin__bez_button plugin__bez_button_red">
                        <?php echo $tpl->getLang('cancel') ?>
                    </a>
                <?php endif ?>

                <?php if ($tpl->get('thread')->can_add_comments()): ?>
                    <button class="plugin__bez_button plugin__bez_button_green" name="fn" value="comment_add">
                        <?php echo $tpl->param('kid') != '' ? $tpl->getLang('correct') : $tpl->getLang('add') ?>
                    </button>
                <?php endif ?>
                <?php if ($tpl->param('kid') == '' && $tpl->get('thread')->acl_of('state') >= BEZ_PERMISSION_CHANGE): ?>
                    <?php if ($tpl->get('thread')->can_be_closed()): ?>
                        <button class="plugin__bez_button plugin__bez_button_gray" name="fn" value="thread_close">
                            <?php echo $tpl->getLang('js')['close_issue' . $tpl->get('lang_suffix')] ?>
                        </button>
                    <?php elseif ($tpl->get('thread')->can_be_rejected()): ?>
                        <button class="plugin__bez_button plugin__bez_button_gray" name="fn" value="thread_reject">
                            <?php echo $tpl->getLang('js')['reject_issue' . $tpl->get('lang_suffix')] ?>
                        </button>
                    <?php elseif ($tpl->get('thread')->can_be_reopened()): ?>
                        <button class="plugin__bez_button plugin__bez_button_gray" name="fn" value="thread_reopen">
                            <?php echo $tpl->getLang('js')['reopen_issue'. $tpl->get('lang_suffix')]  ?>
                        </button>
                    <?php endif ?>
                <?php endif ?>
                </div>
                </div>
		</div>
        <?php if (  $tpl->param('kid') != '' &&
                    $tpl->get('thread_comment')->task_count > 0): ?>
            <div style="margin-top: 10px; margin-left: 40px">
                <?php foreach ($tpl->get('tasks ' . $tpl->get('thread_comment')->id, array()) as $task): ?>
                    <?php $tpl->set('task', $task) ?>
                    <?php if (	$tpl->param('action') == 'task_edit' &&
                        $tpl->param('tid') == $task->id): ?>
                        <?php include 'task_form.php' ?>
                    <?php else: ?>
                        <?php include 'task_box.php' ?>
                    <?php endif ?>
                <?php endforeach ?>
            </div>
        <?php endif ?>
    </div>
    </div>
</form>
