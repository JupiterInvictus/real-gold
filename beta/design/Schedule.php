<?php

const NEW_HIRE_DAYS = 31;

class User {
	protected $username;
	protected $name;
	protected $hiredate;
	protected $supervisor;
	protected $leavedate;

	public function __construct($username, $name, $hiredate, $supervisor = '') {
		$this->username = $username;
		$this->name = $name;
		$this->hiredate = new DateTime($hiredate);
		$this->supervisor = $supervisor;
	}

	// Identify whether user is a new hire. Optional date supplied, defaults to today.
	public function isNewHire($date = "") {
		if ($date == '') { $date = date("Y-m-d"); }
		$when = new DateTime($date);
		if ($when->diff($this->hiredate)->format("%a") < NEW_HIRE_DAYS) { return true; }
		return false;
	}

	public function offBoard(DateTime $leavedate) {
		$this->leavedate = $leavedate;
	}

	public function isLeaver() {
		//if ($this->leavedate )
		return true;
	}
}


class ActivityCode {
	protected $code;
	protected $icon;
	protected $name;
	protected $accessLevelRequired;

	public function __construct($code, $name, $icon, $accessLevelRequired = 1) {
		$this->code = $code;
		$this->name = $name;
		$this->icon = $icon;
		$this->accessLevelRequired = $accessLevelRequired;
	}
}

class Activity {
	protected $startTime;
	protected $endTime;
	protected $activityCode;

	public function __construct($startTime, $endTime, ActivityCode $activityCode) {
		$this->startTime = $startTime;
		$this->endTime = $endTime;
		$this->activityCode = $activityCode;
	}

	public function getName() {
		return $this->activityCode->getName();
	}
}

class Schedule {
	protected $date;
	protected $dateAdded;
	protected $employee;
	protected $generatedBy;
	protected $activities = [];

	public function __construct($date, User $employee, User $generatedBy) {
		$this->date = $date;
		$this->employee = $employee;
		$this->generatedBy = $generatedBy;
		$this->dateAdded = date("Y-m-d");
	}

	public function addActivity(Activity $activity) {
		array_push($this->activities, $activity);
		return true;
	}
}

$joakim = new User('jsaettem', 'Joakim Saettem', '2012-08-28');
$fredrik = new User('florichs', 'Fredrik Lorichs', '2015-06-28', $joakim);

$opentime = new ActivityCode('open', 'Open Time', 'open-time');
$schedule = new Schedule("2018-04-2", $fredrik, $joakim);
$schedule->addActivity(new Activity('3:00 PM', '3:15 PM', $opentime));
