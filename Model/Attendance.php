<?php
/*
 * Ripple  Project
 *
 * @author        Enfu Guo
 * @copyright     NPO Organization uec support
 * @link          http://uecsupport.dip.jp/
 * @license       http://www.gnu.org/licenses/gpl-3.0.en.html GPL License
*/
App::uses('AppModel', 'Model');
App::import('Model', 'Group');
App::import('Model', 'User');
App::import('Model', 'Date');
App::import('Model', 'Lesson');
App::import('Model', 'Log');
/**
 * Attendance Model
 *
 * @property User $User
 * @property Date $Date
 * @property Lesson $Lesson
 */
class Attendance extends AppModel {


	// The Associations below have been created with all possible keys, those that are not needed can be removed

/**
 * belongsTo associations
 *
 * @var array
 */
	public $belongsTo = array(
		'User' => array(
			'className' => 'User',
			'foreignKey' => 'user_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		),
		'Date' => array(
			'className' => 'Date',
			'foreignKey' => 'date_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		)
	);

	/**
	 * 検索用
	 */
	public $actsAs = array(
		'Search.Searchable'
	);

	public $filterArgs = array(
	);


	public function isExistAttendanceInfo($date_id){
		$data = $this->find('first', array(
			'conditions' => array('date_id' => $date_id)
		));
		if($data){ return true; }
		return false;
	}

	public function getAttendanceInfo(){
		//今日の日付を生成
    $today = date("Y-m-d");
		$this->set('today', $today);

		$conditions = [];
		$conditions['Attendance.created BETWEEN ? AND ?'] = array(
			$today,
			$today.' 23:59:59'
    );
		$attendanceinfo = $this->find('all',array(
			'conditions' => $conditions
		));

		$isInfoSet = $attendanceinfo != NULL ? true : false;

		return $isInfoSet;
	}

	public function setAttendanceInfo($date_id){
		//$user_model = new User();
		$user_list = $this->User->find('all',array(
			'conditions' => array(
				'User.role' => 'user'
			),
			'order' => 'User.id ASC'
		));
		foreach($user_list as $user){
			$init_info = [];
			$init_info = array(
				'user_id' => $user['User']['id'],
				'period'  => $user['User']['period'],
				'date_id' => $date_id,
				'status'  => 2
			);
			$this->create();
			$this->save($init_info);
		}
	}

	public function setNewUserAttendanceInfo($date_id){
		$user_list = $this->User->find('all',array(
			'conditions' => array(
				'User.role' => 'user'
			),
			'order' => 'User.id ASC'
		));
		foreach($user_list as $user){
			$user_id = $user['User']['id'];
			$user_info = $this->find('all',array(
				'conditions' => array(
					'User.id' => $user_id,
					'Date.id' => $date_id
				)
			));
			if(!isset($user_info[0])){
				$init_info = array(
					'user_id' => $user['User']['id'],
					'period'  => $user['User']['period'],
					'date_id' => $date_id,
					'status'  => 2
				);
				$this->create();
				$this->save($init_info);
			}
		}
	}

	public function getAllTimeAttendances($user_id){
		$data = $this->find('all',array(
			'conditions' => array(
				'Attendance.user_id' => $user_id
			),
			'order' => array(
				'Date.date' => 'DESC'
			),
			'limit' => 8,
			'recursive' => 0
		));
		return $data;
	}

	public function findRecentAttendances($user_id){
		$today=date('Y-m-d', strtotime('+6 days'));
		$data = $this->find('all',array(
			'conditions' => array(
				'Attendance.user_id' => $user_id,
				'Date.date < ?' => $today.' 23:59:59'
			),
			'order' => array(
				'Date.date' => 'DESC'
			),
			'limit' => 8,
			'recursive' => 0
		));
		return $data;
	}

	// user_idと過去8回分出欠席の配列を作る
	public function findAllUserAttendances(){
		$user_list = $this->User->find('all',array(
			'conditions' => array(
				'User.role' => 'user',
				''
			),
			'order' => 'User.id ASC'
		));
		$attendance_list = array();
		foreach($user_list as $user){
			$user_id = $user['User']['id'];
			$recent_attendance = $this->findRecentAttendances($user_id);
			$attendance_list += [$user_id => $recent_attendance];
		}
		return $attendance_list;
	}

	public function findAttendanceDate($attendance_id, $format_str='Y-m-d'){
		$data = $this->find('first', array(
			'conditions' => array('Attendance.id' => $attendance_id),
			'recursive' => 0
		));
		$attendance_date = (new DateTime($data['Date']['date']))->format($format_str);
		return $attendance_date;
	}

	public function findLoginTime($attendance_id, $format_str='H:i:s'){
		$data = $this->find('first', array(
			'fields' => array('id', 'login_time'),
			'conditions' => array('id' => $attendance_id),
			'recursive' => -1
		));
		$login_time = $data['Attendance']['login_time'];
		if($login_time == null){return null;}
		return (new DateTime($login_time))->format($format_str);
	}

	public function findStandardIP(){
		$this->Group = new Group();
		$this->User  = new User();
		$this->Log   = new Log();

		//スタッフグループのidを探す
		$staff_group_info = $this->Group->find('first',array(
			'conditions' => array(
				'Group.title like' => 'スタッフ'
			)
		));
		$staff_group_id = $staff_group_info['Group']['id'];

		//スタッフグループに所属するメンバーリストを探す
		$staff_member_list = $this->User->find('list',array(
			'conditions' => array(
				'User.group_id' => $staff_group_id
			),
			'fields' => array(
				'User.id',
				'User.id'
			),
			'order' => array(
				'User.id ASC'
			)
		));

		//メンバーのログインipを探し，当日の基準ipを決める
		$staff_member_last_login_ip_list = [];
		foreach ($staff_member_list as $row){
			$row_info = $this->Log->find('first',array(
				'conditions' => array(
					'Log.user_id' => $row
				),
				'order' => array(
					'Log.created' => 'desc'
				)
			));
			$row_ip = $row_info['Log']['user_ip'];
			$staff_member_last_login_ip_list[$row] = (string)$row_ip;
		}

		$ip_count = array_count_values($staff_member_last_login_ip_list);
		$standard = array_keys($ip_count, max($ip_count));
		$standard_ip = $standard[0];
		return $standard_ip;
	}

	public function calcLateTime($date_id, $login_time){
		$this->Date   = new Date();
		$this->Lesson = new Lesson();

		$login_time = (int)strtotime($login_time);
		$lesson_date = $this->Date->getDate($date_id);
		$lessons = $this->Lesson->findLessons($date_id);

		foreach($lessons as $lesson){
			$period = $lesson['Lesson']['period'];
			$start  = (int)strtotime($lesson_date.' '.$lesson['Lesson']['start']);
			$end    = (int)strtotime($lesson_date.' '.$lesson['Lesson']['end']);

			if($login_time <= $start){
				$late_time = 0;
				return $late_time;
			} else if($login_time <= $end){
				$late_time = (int)(($login_time - $start) / 60);
				return $late_time;
			}
		}
		$late_time = null;
		return $late_time;
	}

	public function takeAttendance($user_id, $user_ip){
		$this->Date   = new Date();
		$this->Lesson = new Lesson();
		if(!$this->Date->isClassDate()){ return null; }

		$today_date_id = $this->Date->getTodayClassId();
		$is_online_class = $this->Date->isOnlineClass();
		if($is_online_class){
			$today_attendance_info = $this->find('first', array(
				'conditions' => array(
					'user_id' => $user_id,
					'date_id' => $today_date_id
				),
				'recursive' => -1
			));

			$save_info = $today_attendance_info['Attendance'];
			if($save_info['status'] != 1){  // 元の出欠情報が出席済以外なら
				$now_time = date('Y-m-d H:i:s');
				$now_time_int = (int)strtotime($now_time);

				$lesson_date = $this->Date->getDate($today_date_id);
				$lessons = $this->Lesson->findLessons($today_date_id);
				foreach($lessons as $lesson){
					$period = $lesson['Lesson']['period'];
					$start                  = (int)strtotime($lesson_date.' '.$lesson['Lesson']['start']);
					$half_hour_before_start = (int)strtotime($lesson_date.' '.$lesson['Lesson']['start'].' -30 minute');
					$half_hour_after_start  = (int)strtotime($lesson_date.' '.$lesson['Lesson']['start'].' +30 minute');
					$end                    = (int)strtotime($lesson_date.' '.$lesson['Lesson']['end']);
					if($half_hour_before_start <= $now_time_int && $now_time_int <= $half_hour_after_start){
						$save_info['status'] = 1;
						$save_info['login_time'] = $now_time;
						$save_info['late_time'] = 0;
						$this->save($save_info);
						return;
					}else if($half_hour_after_start < $now_time_int && $now_time_int < $end){
						$save_info['status'] = 1;
						$save_info['login_time'] = $now_time;
						$save_info['late_time'] = (int)(($now_time_int - $start) / 60);
					}
				}
				if($save_info['status'] == 1){ $this->save($save_info); }
			}
		}else{
			$standard_ip = $this->findStandardIP();
			if($user_ip != $standard_ip){ return null; }

			$today_attendance_info = $this->find('first', array(
				'conditions' => array(
					'user_id' => $user_id,
					'date_id' => $today_date_id
				),
				'recursive' => -1
			));

			$save_info = $today_attendance_info['Attendance'];
			if($save_info['status'] != 1){  // 元の出欠情報が出席済以外なら
				$save_info['status'] = 1;

				$login_time = date('Y-m-d H:i:s');
				$save_info['login_time'] = $login_time;
				$save_info['late_time'] = $this->calcLateTime($today_date_id, $login_time);
				$this->save($save_info);
			}
		}
	}

}
