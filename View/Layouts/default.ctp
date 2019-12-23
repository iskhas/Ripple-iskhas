<?php
/**
 * iroha Board Project
 *
 * @author        Kotaro Miura
 * @copyright     2015-2016 iroha Soft, Inc. (http://irohasoft.jp)
 * @link          http://irohaboard.irohasoft.jp
 * @license       http://www.gnu.org/licenses/gpl-3.0.en.html GPL License
 */

?>
<!DOCTYPE html>
<html lang="ja">
<head>
	<?php echo $this->Html->charset(); ?>

	<title><?php echo h($this->Session->read('Setting.title')); ?></title>
	<meta name="application-name" content="iroha Board">
	<meta http-equiv="X-UA-Compatible" content="IE=edge"/>
	<?php
		// 管理画面フラグ（ログイン画面は例外とする）
		$is_admin_page = (($this->params['admin']==1)&&($this->params['action']!='admin_login'));

		// 受講者向け画面及び、管理システムのログイン画面のみ viewport を設定（スマートフォン対応）
		if(!$is_admin_page)
			echo '<meta name="viewport" content="width=device-width,initial-scale=1">';

		echo $this->Html->meta('icon');

		echo $this->Html->css('cake.generic');
		echo $this->Html->css('jquery-ui');
		echo $this->Html->css('bootstrap.min');
		echo $this->Html->css('common.css?20190401');

		// 管理画面用CSS
		if($is_admin_page){
			echo $this->Html->css('admin.css?20190401');
		}else{
			echo $this->Html->css('user.css?20191008');
		}
		// カスタマイズ用CSS
		echo $this->Html->css('custom.css?20190401');

		echo $this->Html->script('jquery-1.9.1.min.js');
		echo $this->Html->script('jquery-ui-1.9.2.min.js');
		echo $this->Html->script('bootstrap.min.js');
		echo $this->Html->script('moment.js');
		echo $this->Html->script('common.js?20190401');

		// 管理画面用スクリプト
		if($is_admin_page)
			echo $this->Html->script('admin.js?20190401');

		// デモモード用スクリプト
		if(Configure::read('demo_mode'))
			echo $this->Html->script('demo.js');

		// カスタマイズ用スクリプト
		echo $this->Html->script('custom.js?20190401');

		echo $this->fetch('meta');
		echo $this->fetch('css');
		echo $this->fetch('script');
		echo $this->fetch('css-embedded');
		echo $this->fetch('script-embedded');
	?>
	<style>
		.ib-theme-color
		{
			background-color	: <?php echo h($this->Session->read('Setting.color')); ?>;
			color				: white;
		}

		.ib-logo a
		{
			color				: white;
			text-decoration		: none;
		}
	</style>
</head>
<body>
	<nav class="navbar navbar-expand-sm navbar-dark" style="background-color: <?php echo h($this->Session->read('Setting.color')); ?>;">
	<?php $top_url = (($loginedUser['role']=='admin') && (!$is_admin_page)) ? '/admin/recentstates' : '/'; ?>
	<a class="navbar-brand" href="<?php echo $this->Html->url($top_url)?>"><?php echo h($this->Session->read('Setting.title')); ?></a>
	<button type="button" class="navbar-toggler" data-toggle="collapse" data-target="#Navber" aria-controls="Navber" aria-expanded="false" aria-label="ナビゲーションの切替">
		<span class="navbar-toggler-icon"></span>
	</button>
	<?php if(@$loginedUser) {?>
	<div class="collapse navbar-collapse" id="Navber">
		<ul class="navbar-nav mr-auto mt-2 mt-lg-0">
			<?php if($is_admin_page) {?>
			<?php $is_active = ($this->name=='RecentStates') ? ' active' : ''; ?>
			<li class="nav-item dropdown <?php echo $is_active; ?>">
				<a href="#" class="nav-link dropdown-toggle" id="navbarDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
					受講生近況
				</a>
				<div class="dropdown-menu" aria-labelledby="navbarDropdown">
					<?php echo $this->Html->link(__('グループ'), array('controller' => 'recentstates', 'action' => 'find_by_group'), array('class' => 'dropdown-item')); ?>
					<?php echo $this->Html->link(__('個人'), array('controller' => 'recentstates', 'action' => 'find_by_student'), array('class' => 'dropdown-item')); ?>
					<?php echo $this->Html->link(__('全受講生'), array('controller' => 'recentstates', 'action' => 'admin_all_view'), array('class' => 'dropdown-item')); ?>
				</div>
			</li>
			<?php
			$is_active = ($this->name=='Data' or $this->name=='Records' or $this->name=='SoapRecords' or
									$this->name=='Enquete' or $this->name=='Attendances' or
									($this->name=='AdminManages' && $this->action=='admin_download')) ? ' active' : '';
			?>
			<li class="nav-item dropdown <?php echo $is_active; ?>">
				<a href="#" class="nav-link dropdown-toggle" id="navbarDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
					データ一覧
				</a>
				<div class="dropdown-menu" aria-labelledby="navbarDropdown">
					<?php echo $this->Html->link(__('学習履歴'), array('controller' => 'records', 'action' => 'index'), array('class' => 'dropdown-item')); ?>
					<?php echo $this->Html->link(__('SOAP'), array('controller' => 'soaprecords', 'action' => 'index'), array('class' => 'dropdown-item')); ?>
					<?php echo $this->Html->link(__('アンケート'), array('controller' => 'enquete', 'action' => 'index'), array('class' => 'dropdown-item')); ?>
					<?php echo $this->Html->link(__('出欠席'), array('controller' => 'attendances', 'action' => 'index'), array('class' => 'dropdown-item')); ?>
					<?php echo $this->Html->link(__('授業データ'), array('controller' => 'adminmanages', 'action' => 'download'), array('class' => 'dropdown-item')); ?>
				</div>
			</li>
			<?php $is_active = ($this->name=='Soaps') ? ' active' : ''; ?>
			<li class="nav-item dropdown <?php echo $is_active; ?>">
				<a href="#" class="nav-link dropdown-toggle" id="navbarDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
					SOAP記入
				</a>
				<div class="dropdown-menu" aria-labelledby="navbarDropdown">
					<?php echo $this->Html->link(__('グループ'), array('controller' => 'soaps', 'action' => 'find_by_group'), array('class' => 'dropdown-item')); ?>
					<?php echo $this->Html->link(__('個人'), array('controller' => 'soaps', 'action' => 'find_by_student'), array('class' => 'dropdown-item')); ?>
				</div>
			</li>
			<?php
			$is_active = ($this->name=='Managements' or $this->name=='Settings' or
										$this->name=='Users' or $this->name=='Groups' or
										$this->name=='Courses' or $this->name=='Infos' or
										($this->name=='AdminManages' && $this->action=='admin_index')) ? ' active' : '';
			?>
			<li class="nav-item dropdown <?php echo $is_active; ?>">
				<a href="#" class="nav-link dropdown-toggle" id="navbarDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
					各種管理
				</a>
				<div class="dropdown-menu" aria-labelledby="navbarDropdown">
					<?php echo $this->Html->link(__('ユーザ'), array('controller' => 'users', 'action' => 'index'), array('class' => 'dropdown-item')); ?>
					<?php echo $this->Html->link(__('グループ'), array('controller' => 'groups', 'action' => 'index'), array('class' => 'dropdown-item')); ?>
					<?php echo $this->Html->link(__('コース'), array('controller' => 'courses', 'action' => 'index'), array('class' => 'dropdown-item')); ?>
					<div class="dropdown-divider"></div>
					<?php echo $this->Html->link(__('その他管理'), array('controller' => 'managements', 'action' => 'other_index'), array('class' => 'dropdown-item')); ?>
				</div>
			</li>
			<?php } else {?>
			<?php $is_active = ($this->name=='UsersCourses') ? ' active' : ''; ?>
			<li class="nav-item <?php echo $is_active; ?>">
				<?php echo $this->Html->link(__('ダッシュボード'), '/', array('class' => 'nav-link')); ?>
			</li>
			<?php $is_active = ($this->name=='Enquete') ? ' active' : ''; ?>
			<li class="nav-item dropdown <?php echo $is_active; ?>">
				<a href="#" class="nav-link dropdown-toggle" id="navbarDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
					アンケート
				</a>
				<div class="dropdown-menu" aria-labelledby="navbarDropdown">
					<?php echo $this->Html->link(__('記入'), array('controller' => 'enquete', 'action' => 'index'), array('class' => 'dropdown-item')); ?>
					<?php echo $this->Html->link(__('履歴'), array('controller' => 'enquete', 'action' => 'records'), array('class' => 'dropdown-item')); ?>
				</div>
			</li>
			<?php }?>
		</ul>
		<ul class="navbar-nav mt-2 mt-lg-0">
			<li class="nav-item dropdown">
				<a href="#" class="nav-link dropdown-toggle" id="navbarDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
					<?php echo h($loginedUser["name"]); ?>
				</a>
				<div class="dropdown-menu dropdown-menu-right" aria-labelledby="navbarDropdown">
					<?php echo $this->Html->link(__('設定'), array('controller' => 'users', 'action' => 'setting'), array('class' => 'dropdown-item')); ?>
					<div class="dropdown-divider"></div>
					<?php echo $this->Html->link(__('ログアウト'), $logoutURL, array('class' => 'dropdown-item')); ?>
				</div>
			</li>
		</ul>
	</div><!-- /.navbar-collapse -->
	<?php }?>
	</nav>

	<div id="container">
		<div id="header" class="row">
			<?php //echo $this->fetch('menu'); ?>
		</div>
		<div id="content" class="row">
			<?php echo $this->Session->flash(); ?>

			<?php echo $this->fetch('content'); ?>
		</div>
		<div id="footer" class="row">
		</div>
	</div>

	<div class="ib-theme-color text-center">
		<?php echo h($this->Session->read('Setting.copyright')); ?>
	</div>

	<div class="irohasoft">
		Powered by <a href="http://irohaboard.irohasoft.jp/">iroha Board</a>
	</div>

	<?php echo $this->element('sql_dump'); ?>
</body>
</html>
