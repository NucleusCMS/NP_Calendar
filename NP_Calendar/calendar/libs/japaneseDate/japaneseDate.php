<?php
// +----------------------------------------------------------------------+
// |                          Japanese Date                               |
// +----------------------------------------------------------------------+
// | PHP Version 4・5                                                     |
// +----------------------------------------------------------------------+
// | Copyright (c) 2002-2006 The Artisan Member                           |
// +----------------------------------------------------------------------+
// | Authors: Akito<akito-artisan@five-foxes.com>                         |
// +----------------------------------------------------------------------+
//
/**
 * 日本語/和暦日付クラスメインファイル
 *
 * @package JapaneseDate
 * @version 1.7
 * @since 0.1
 * @author Akito<akito-artisan@five-foxes.com>
 */

/**
 * 旧暦クラス名
 */
const JD_LC_CLASS_NAME = 'japaneseDate_lunarCalendar';

/**
 * 旧暦クラスパス
 */
define("JD_LC_CLASS_PATH", dirname(__FILE__).DIRECTORY_SEPARATOR."lunarCalendar.php");

/**
 * 祝日定数
 */
const JD_NO_HOLIDAY = 0;
const JD_NEW_YEAR_S_DAY = 1;
const JD_COMING_OF_AGE_DAY = 2;
const JD_NATIONAL_FOUNDATION_DAY = 3;
const JD_THE_SHOWA_EMPEROR_DIED = 4;
const JD_VERNAL_EQUINOX_DAY = 5;
const JD_DAY_OF_SHOWA = 6;
const JD_GREENERY_DAY = 7;
const JD_THE_EMPEROR_S_BIRTHDAY = 8;
const JD_CROWN_PRINCE_HIROHITO_WEDDING = 9;
const JD_CONSTITUTION_DAY = 10;
const JD_NATIONAL_HOLIDAY = 11;
const JD_CHILDREN_S_DAY = 12;
const JD_COMPENSATING_HOLIDAY = 13;
const JD_CROWN_PRINCE_NARUHITO_WEDDING = 14;
const JD_MARINE_DAY = 15;
const JD_AUTUMNAL_EQUINOX_DAY = 16;
const JD_RESPECT_FOR_SENIOR_CITIZENS_DAY = 17;
const JD_SPORTS_DAY = 18;
const JD_CULTURE_DAY = 19;
const JD_LABOR_THANKSGIVING_DAY = 20;
const JD_REGNAL_DAY = 21;

/**
 * 特定月定数
 */
const JD_VERNAL_EQUINOX_DAY_MONTH = 3;
const JD_AUTUMNAL_EQUINOX_DAY_MONTH = 9;

/**
 * 曜日定数
 */
const JD_SUNDAY = 0;
const JD_MONDAY = 1;
const JD_TUESDAY = 2;
const JD_WEDNESDAY = 3;
const JD_THURSDAY = 4;
const JD_FRIDAY = 5;
const JD_SATURDAY = 6;


/**
 * 日本語/和暦日付クラス
 *
 * @package JapaneseDate
 * @version 2.0
 * @since 0.1
 * @author Akito<akito-artisan@five-foxes.com>
 */
class japaneseDate
{
	/**
	 * 旧暦クラスオブジェクト
	 * @var object
	 */
	var $kyureki;
	
	/**#@+
	 * @access private
	 */
	var $_holiday_name = array(
		0 => '',
		1 => '元旦',
		2 => '成人の日',
		3 => '建国記念の日',
		4 => '昭和天皇の大喪の礼',
		5 => '春分の日',
		6 => '昭和の日',
		7 => 'みどりの日',
		8 => '天皇誕生日',
		9 => '皇太子明仁親王の結婚の儀',
		10 => '憲法記念日',
		11 => '国民の休日',
		12 => 'こどもの日',
		13 => '振替休日',
		14 => '皇太子徳仁親王の結婚の儀',
		15 => '海の日',
		16 => '秋分の日',
		17 => '敬老の日',
		18 => '体育の日',
		19 => '文化の日',
		20 => '勤労感謝の日',
		21 => '即位礼正殿の儀',
	);
	
	var $_weekday_name = array('日', '月', '火', '水', '木', '金', '土');
	
	var $_during_the_war_period_weekday_name = array('月', '月', '火', '水', '木', '金', '金');
	
	var $_month_name = array('', '睦月', '如月', '弥生', '卯月', '皐月', '水無月', '文月', '葉月', '長月', '神無月', '霜月', '師走');
	
	var $_six_weekday = array('大安', '赤口', '先勝', '友引', '先負', '仏滅');
	
	var $_oriental_zodiac = array('亥', '子', '丑', '寅', '卯', '辰', '巳', '午', '未', '申', '酉', '戌',);
	
	var $_era_name = array('昭和', '平成');
	
	var $_era_calc = array(1925, 1988);
	
	var $_24_sekki = array();
	
	
	
	/**#@-*/
	
	/**
	 * コンストラクタ
	 *
	 * @return void
	*/
	function __construct()
	{
		// 旧暦取り扱いクラス
		include_once(JD_LC_CLASS_PATH);
		$lc = JD_LC_CLASS_NAME;
		$this->lc = new $lc();
		
	}
	
	/**
	 * 指定月の祝日リストを取得する
	 *
	 * @param int $time_stamp タイムスタンプ
	 * @return array
	 */
	function getHolidayList($time_stamp)
	{
		switch ($this->getMonth($time_stamp)) {
			case 1:
			return $this->getJanuaryHoliday($this->getYear($time_stamp));
			case 2:
			return $this->getFebruaryHoliday($this->getYear($time_stamp));
			case 3:
			return $this->getMarchHoliday($this->getYear($time_stamp));
			case 4:
			return $this->getAprilHoliday($this->getYear($time_stamp));
			case 5:
			return $this->getMayHoliday($this->getYear($time_stamp));
			case 6:
			return $this->getJuneHoliday($this->getYear($time_stamp));
			case 7:
			return $this->getJulyHoliday($this->getYear($time_stamp));
			case 8:
			return $this->getAugustHoliday($this->getYear($time_stamp));
			case 9:
			return $this->getSeptemberHoliday($this->getYear($time_stamp));
			case 10:
			return $this->getOctoberHoliday($this->getYear($time_stamp));
			case 11:
			return $this->getNovemberHoliday($this->getYear($time_stamp));
			case 12:
			return $this->getDecemberHoliday($this->getYear($time_stamp));
		}
		return array();
	}
	
	/**
	 * 干支キーを返す
	 *
	 * @param int $time_stamp タイムスタンプ
	 * @return int
	 */
	function getOrientalZodiac($time_stamp)
	{
        return ($this->getYear($time_stamp)+9)%12;
	}
	
	/**
	 * 年号キーを返す
	 *
	 * @param int $time_stamp タイムスタンプ
	 * @return int
	 */
	function getEraName($time_stamp)
	{
		if (mktime(0, 0, 0, 1 , 7, 1989) >= $time_stamp) {
			//昭和
			return 0;
		}
        //平成
        return 1;
    }

	/**
	 * 和暦を返す
	 *
	 * @param int $time_stamp タイムスタンプ
	 * @param int 和暦モード(空にすると、自動取得)
	 * @return int
	 */
	function getEraYear($time_stamp, $key = -1)
	{
		if ($key == -1) {
			$key = $this->getEraName($time_stamp);
		}
		return $this->getYear($time_stamp)-$this->_era_calc[$key];
	}
	
	/**
	 * 日本語フォーマットされた休日名を返す
	 *
	 * @param int $key 休日キー
	 * @return string
	 */
	function viewHoliday($key)
	{
		return $this->_holiday_name[$key];
	}
	
	/**
	 * 日本語フォーマットされた曜日名を返す
	 *
	 * @param int $key 曜日キー
	 * @return string
	 */
	function viewWeekday($key)
	{
		return $this->_weekday_name[$key];
	}
	
	
	/**
	 * 日本語フォーマットされた旧暦月名を返す
	 *
	 * @param int $key 月キー
	 * @return string
	 */
	function viewMonth($key)
	{
		return $this->_month_name[$key];
	}
	
	
	/**
	 * 日本語フォーマットされた六曜名を返す
	 *
	 * @param int $key 六曜キー
	 * @return string
	 */
	function viewSixWeekday($key)
	{
		return array_key_exists($key, $this->_six_weekday) ? $this->_six_weekday[$key] : "";
	}
	
	
	/**
	 * 日本語フォーマットされた戦争中曜日名を返す
	 *
	 * @param int $key 曜日キー
	 * @return string
	 */
	function viewWarWeekday($key)
	{
		return $this->during_the_war_period_weekday_name[$key];
	}
	
	/**
	 * 日本語フォーマットされた干支を返す
	 *
	 * @param int $key 干支キー
	 * @return string
	 */
	function viewOrientalZodiac($key)
	{
		return $this->_oriental_zodiac[$key];
	}
	
	/**
	 * 日本語フォーマットされた年号を返す
	 *
	 * @param int $key 年号キー
	 * @return string
	 */
	function viewEraName($key)
	{
		return $this->_era_name[$key];
	}
	
	/**
	 * 春分の日を取得
	 *
	 * @param int $time_stamp タイムスタンプ
	 * @return int タイムスタンプ
	 */
	function getVrenalEquinoxDay($year)
	{
		if ($year <= 1979) {
			$day = floor(20.8357 + (0.242194 * ($year - 1980)) - floor(($year - 1980) / 4));
		} elseif ($year <= 2099) {
			$day = floor(20.8431 + (0.242194 * ($year - 1980)) - floor(($year - 1980) / 4));
		} elseif ($year <= 2150) {
			$day = floor(21.851 + (0.242194 * ($year - 1980)) - floor(($year - 1980) / 4));
		} else {
			return false;
		}
		return mktime(0, 0, 0, JD_VERNAL_EQUINOX_DAY_MONTH, $day, $year);
	}
	
	/**
	 * 秋分の日を取得
	 *
	 * @param int $time_stamp タイムスタンプ
	 * @return int タイムスタンプ
	 */
	function getAutumnEquinoxDay($year)
	{
		if ($year <= 1979) {
			$day = floor(23.2588 + (0.242194 * ($year - 1980)) - floor(($year - 1980) / 4));
		} elseif ($year <= 2099) {
			$day = floor(23.2488 + (0.242194 * ($year - 1980)) - floor(($year - 1980) / 4));
		} elseif ($year <= 2150) {
			$day = floor(24.2488 + (0.242194 * ($year - 1980)) - floor(($year - 1980) / 4));
		} else {
			return false;
		}
		return mktime(0, 0, 0, JD_AUTUMNAL_EQUINOX_DAY_MONTH, $day, $year);
	}
	
	/**
	 * タイムスタンプを展開して、日付の詳細配列を取得する
	 *
	 * @param int $time_stamp タイムスタンプ
	 * @return array タイムスタンプ
	 */
	function makeDateArray($time_stamp)
	{
		$res = array(
			'Year'    => $this->getYear($time_stamp),
			'Month'   => $this->getMonth($time_stamp),
			'Day'     => $this->getDay($time_stamp),
			'Weekday' => $this->getWeekday($time_stamp),
		);
		
		$holiday_list = $this->getHolidayList($time_stamp);
		$res["Holiday"] = isset($holiday_list[$res["Day"]]) ? $holiday_list[$res["Day"]] : JD_NO_HOLIDAY;
		return $res;
	}
	
	/**
	 * 七曜を数値化して返します
	 *
	 * @param int $time_stamp タイムスタンプ
	 */
	function getWeekday($time_stamp)
	{
		return date('w', $time_stamp);
	}

	/**
	 * 年を数値化して返します
	 *
	 * @param int $time_stamp タイムスタンプ
	 */
	function getYear($time_stamp)
	{
		return date('Y', $time_stamp);
	}

	/**
	 * 月を数値化して返します
	 *
	 * @param int $time_stamp タイムスタンプ
	 */
	function getMonth($time_stamp)
	{
		return date('n', $time_stamp);
	}
	
	/**
	 * 日を数値化して返します
	 *
	 * @param int $time_stamp タイムスタンプ
	 */
	function getDay($time_stamp)
	{
		return date('j', $time_stamp);
	}
	
	/**
	 * 日を表示用フォーマットで返します
	 *
	 * @param int $time_stamp タイムスタンプ
	 */
	function getStrDay($time_stamp)
	{
		return date('d', $time_stamp);
	}
	
	/**
	 * 六曜を数値化して返します
	 *
	 * @param int $time_stamp タイムスタンプ
	 */
	function getSixWeekday($time_stamp)
	{
		return (date('j', $time_stamp)+date("m", $time_stamp)) % 6;
	}
	
	/**
	 * 祝日判定ロジック一月
	 *
	 * @param int $year 年
	 * @return array
	 */
	function getJanuaryHoliday($year)
	{
		$res[1] = JD_NEW_YEAR_S_DAY;
		//振替休日確認
		if ($this->getWeekDay(mktime(0, 0, 0, 1, 1, $year)) == JD_SUNDAY) {
			$res[2] = JD_COMPENSATING_HOLIDAY;
		}
		if ($year >= 2000) {
			//2000年以降は第二月曜日に変更
			$second_monday = $this->getDayByWeekly($year, 1, JD_MONDAY, 2);
			$res[$second_monday] = JD_COMING_OF_AGE_DAY;
			
		} else {
			$res[15] = JD_COMING_OF_AGE_DAY;
			//振替休日確認
			if ($this->getWeekDay(mktime(0, 0, 0, 1, 15, $year)) == JD_SUNDAY) {
				$res[16] = JD_COMPENSATING_HOLIDAY;
			}
		}
		return $res;
	}
	
	/**
	 * 祝日判定ロジック二月
	 *
	 * @param int $year 年
	 * @return array
	 */
	function getFebruaryHoliday($year)
	{
		$res[11] = JD_NATIONAL_FOUNDATION_DAY;
		//振替休日確認
		if ($this->getWeekDay(mktime(0, 0, 0, 2, 11, $year)) == JD_SUNDAY) {
			$res[12] = JD_COMPENSATING_HOLIDAY;
		}
		if ($year == 1989) {
			$res[24] = JD_THE_SHOWA_EMPEROR_DIED;
		}
		return $res;
	}
	
	/**
	 * 祝日判定ロジック三月
	 *
	 * @param int $year 年
	 * @return array
	 */
	function getMarchHoliday($year)
	{
		$VrenalEquinoxDay = $this->getVrenalEquinoxDay($year);
		$res[$this->getDay($VrenalEquinoxDay)] = JD_VERNAL_EQUINOX_DAY;
		//振替休日確認
		if ($this->getWeekDay($VrenalEquinoxDay) == JD_SUNDAY) {
			$res[$this->getDay($VrenalEquinoxDay)+1] = JD_COMPENSATING_HOLIDAY;
		}
		return $res;
	}
	
	/**
	 * 祝日判定ロジック四月
	 *
	 * @param int $year 年
	 * @return array
	 */
	function getAprilHoliday($year)
	{
		if ($year == 1959) {
			$res[10] = JD_CROWN_PRINCE_HIROHITO_WEDDING;
		}
		if ($year >= 2007) {
			$res[29] = JD_DAY_OF_SHOWA;
		} elseif ($year >= 1989) {
			$res[29] = JD_GREENERY_DAY;
		} else {
			$res[29] = JD_THE_EMPEROR_S_BIRTHDAY;
		}
		//振替休日確認
		if ($this->getWeekDay(mktime(0, 0, 0, 4, 29, $year)) == JD_SUNDAY) {
			$res[30] = JD_COMPENSATING_HOLIDAY;
		}
		return $res;
	}
	
	/**
	 * 祝日判定ロジック五月
	 *
	 * @param int $year 年
	 * @return array
	 */
	function getMayHoliday($year)
	{
		$res[3] = JD_CONSTITUTION_DAY;
		if ($year >= 2007) {
			$res[4] = JD_GREENERY_DAY;
		} elseif ($year >= 1986) {
			// 5/4が日曜日の場合はそのまま､月曜日の場合はは『憲法記念日の振替休日』(2006年迄)
			if ($this->getWeekday(mktime(0, 0, 0, 5, 4, $year)) > JD_MONDAY) {
				$res[4] = JD_NATIONAL_HOLIDAY;
			} elseif ($this->getWeekday(mktime(0, 0, 0, 5, 4, $year)) == JD_MONDAY)  {
				$res[4] = JD_COMPENSATING_HOLIDAY;
			}
		}
		$res[5] = JD_CHILDREN_S_DAY;
		if ($this->getWeekDay(mktime(0, 0, 0, 5, 5, $year)) == JD_SUNDAY) {
			$res[6] = JD_COMPENSATING_HOLIDAY;
		}
		if ($year >= 2007) {
			// [5/3,5/4が日曜]なら、振替休日
			if ($this->getWeekday(mktime(0, 0, 0, 5, 4, $year)) == JD_SUNDAY || $this->getWeekday(mktime(0, 0, 0, 5, 3, $year)) == JD_SUNDAY) {
				$res[6] = JD_COMPENSATING_HOLIDAY;
			}
		}
		return $res;
	}

	/**
	 * 祝日判定ロジック六月
	 *
	 * @param int $year 年
	 * @return array
	 */
	function getJuneHoliday($year)
	{
		if ($year == '1993') {
			$res[9] = JD_CROWN_PRINCE_NARUHITO_WEDDING;
		} else {
			$res = array();
		}
		return $res;
	}
	
	/**
	 * 営業日を取得します
	 *
	 * @param int int $time_stamp 取得開始日
	 * @param int int $lim_day 取得日数
	 * @param boolean $luna 旧暦情報を使用するかどうか(falseにすると、旧暦情報を取得しない代わりに、より、高速に動作します)
	 * @param int boolean $is_bypass_holiday 祝日を無視するかどうか (optional)
	 * @param int boolean|array $bypass_week_arr 無視する曜日 (optional)
	 * @param int boolean|array $is_bypass_date 無視する日 (optional)
	 * @return array
	 */
	function getWorkingDay($time_stamp, $lim_day, $luna = true, $is_bypass_holiday = true, $bypass_week_arr = false, $is_bypass_date = false )
	{
		if (is_array($bypass_week_arr)) {
			$bypass_week_arr   = array_flip($bypass_week_arr);
		} else {
			$bypass_week_arr = array();
		}
		if (is_array($is_bypass_date)) {
			$gc = array();
			foreach ($is_bypass_date as $value) {
				if (!ereg("^[1-9][0-9]*$", $value)) {
					$value = strtotime($value);
				}
				$gc[mktime(0, 0, 0, date("m", $value), date("d", $value), date("Y", $value))] = 1;
			}
			$is_bypass_date = $gc;
		} else {
			$is_bypass_date = array();
		}
		
		$res = array();
		$i = 0;
		$year  = date('Y', $time_stamp);
		$month = date('m', $time_stamp);
		$day   = date('d', $time_stamp);
		while (count($res) != $lim_day) {
			$time_stamp = mktime(0, 0, 0, $month, $day + $i, $year);
			$gc = $this->purseTime($time_stamp, $luna);
			if (
				(array_key_exists($gc['week'], $bypass_week_arr) == false) &&
				(array_key_exists($gc['time_stamp'], $is_bypass_date) == false) &&
				($is_bypass_holiday ? $gc['holiday'] == JD_NO_HOLIDAY : true)
			) {
				$res[] = $gc;
			}
			$i++;
		}
		return $res;
	}
	
	/**
	 * 祝日判定ロジック七月
	 *
	 * @param int $year 年
	 * @return array
	 */
	function getJulyHoliday($year)
	{
		if ($year >= 2003) {
			$third_monday = $this->getDayByWeekly($year, 7, JD_MONDAY, 3);
			$res[$third_monday] = JD_MARINE_DAY;
		} elseif ($year >= 1996) {
			$res[20] = JD_MARINE_DAY;
			//振替休日確認
			if ($this->getWeekDay(mktime(0, 0, 0, 7, 20, $year)) == JD_SUNDAY) {
				$res[21] = JD_COMPENSATING_HOLIDAY;
			}
		} else {
			$res = array();
		}
		return $res;
	}
	
	/**
	 * 祝日判定ロジック八月
	 *
	 * @param int $year 年
	 * @return array
	 */
	function getAugustHoliday($year)
	{
		return array();
	}

	/**
	 * 祝日判定ロジック九月
	 *
	 * @param int $year 年
	 * @return array
	 */
	function getSeptemberHoliday($year)
	{
		$autumnEquinoxDay = $this->getAutumnEquinoxDay($year);
		$res[$this->getDay($autumnEquinoxDay)] = JD_AUTUMNAL_EQUINOX_DAY;
		//振替休日確認
		if ($this->getWeekDay($autumnEquinoxDay) == 0) {
			$res[$this->getDay($autumnEquinoxDay)+1] = JD_COMPENSATING_HOLIDAY;
		}
		
		if ($year >= 2003) {
			$third_monday = $this->getDayByWeekly($year, 9, JD_MONDAY, 3);
			$res[$third_monday] = JD_RESPECT_FOR_SENIOR_CITIZENS_DAY;
			
			//敬老の日と、秋分の日の間の日は休みになる
			if (($this->getDay($autumnEquinoxDay) - 1) == ($third_monday + 1)) {
				$res[($this->getDay($autumnEquinoxDay) - 1)] = JD_NATIONAL_HOLIDAY;
			}
			
		} elseif ($year >= 1966) {
			$res[15] = JD_RESPECT_FOR_SENIOR_CITIZENS_DAY;
		}
		return $res;
	}
	
	/**
	 * 祝日判定ロジック十月
	 *
	 * @param int $year 年
	 * @return array
	 */
	function getOctoberHoliday($year)
	{
		if ($year >= 2000) {
			//2000年以降は第二月曜日に変更
			$second_monday = $this->getDayByWeekly($year, 10, JD_MONDAY, 2);
			$res[$second_monday] = JD_SPORTS_DAY;
		} elseif ($year >= 1966) {
			$res[10] = JD_SPORTS_DAY;
			//振替休日確認
			if ($this->getWeekDay(mktime(0, 0, 0, 10, 10, $year)) == JD_SUNDAY) {
				$res[11] = JD_COMPENSATING_HOLIDAY;
			}
		}
		return $res;
	}
	
	/**
	 * 祝日判定ロジック十一月
	 *
	 * @param int $year 年
	 * @return array
	 */
	function getNovemberHoliday($year)
	{
		$res[3] = JD_CULTURE_DAY;
		//振替休日確認
		if ($this->getWeekDay(mktime(0, 0, 0, 11, 3, $year)) == JD_SUNDAY) {
			$res[4] = JD_COMPENSATING_HOLIDAY;
		}
		
		if ($year == 1990) {
			$res[12] = JD_REGNAL_DAY;
		}
		
		$res[23] = JD_LABOR_THANKSGIVING_DAY;
		//振替休日確認
		if ($this->getWeekDay(mktime(0, 0, 0, 11, 23, $year)) == JD_SUNDAY) {
			$res[24] = JD_COMPENSATING_HOLIDAY;
		}
		return $res;
	}
	
	/**
	 * 祝日判定ロジック十二月
	 *
	 * @param int $year 年
	 * @return array
	 */
	function getDecemberHoliday($year)
	{
		if ($year >= 1989) {
			$res[23] = JD_THE_EMPEROR_S_BIRTHDAY;
		}
		if ($this->getWeekDay(mktime(0, 0, 0, 12, 23, $year)) == JD_SUNDAY) {
			$res[24] = JD_COMPENSATING_HOLIDAY;
		}
		return $res;
	}
	
	/**
	 * 第○ ■曜日の日付を取得します。
	 *
	 * @param int $year 年
	 * @param int $month 月
	 * @param int $weekly 曜日
	 * @param int $renb 何週目か
	 * @return int
	 */
	function getDayByWeekly($year, $month, $weekly, $renb = 1)
	{
		switch ($weekly) {
			case 0:
				$map = array(7,1,2,3,4,5,6,);
			break;
			case 1:
				$map = array(6,7,1,2,3,4,5,);
			break;
			case 2:
				$map = array(5,6,7,1,2,3,4,);
			break;
			case 3:
				$map = array(4,5,6,7,1,2,3,);
			break;
			case 4:
				$map = array(3,4,5,6,7,1,2,);
			break;
			case 5:
				$map = array(2,3,4,5,6,7,1,);
			break;
			case 6:
				$map = array(1,2,3,4,5,6,7,);
			break;
		}
		
		$renb = 7*$renb+1;
		return $renb - $map[$this->getWeekday(mktime(0,0,0,$month,1,$year))];
	}
	
	/**
	 * 指定月のカレンダー配列を取得します
	 *
	 * @param int $year 年
	 * @param int $month 月
	 * @param boolean $luna 旧暦情報を使用するかどうか(falseにすると、旧暦情報を取得しない代わりに、より、高速に動作します)
	 */
	function getCalendar($year, $month, $luna = true)
	{
		$lim = date("t", mktime(0, 0, 0, $month, 1, $year));
		return $this->getSpanCalendar($year, $month, 1, $lim, $luna);
	}
	
	/**
	 * 指定範囲のカレンダー配列を取得します
	 *
	 * @param int $year 年
	 * @param int $month 月
	 * @param int $str 開始日
	 * @param int $lim 期間(日)
	 * @param boolean $luna 旧暦情報を使用するかどうか(falseにすると、旧暦情報を取得しない代わりに、より、高速に動作します)
	 */
	function getSpanCalendar($year, $month, $str, $lim, $luna = true)
	{
		if ($lim <= 0) {
			return array();
		}
		
		$time_stamp = mktime(0, 0, 0, $month, $str-1, $year);
		if ($luna == false) {
			while ($lim != 0) {
				$time_stamp = mktime(0, 0, 0, date('m', $time_stamp), date('d', $time_stamp) + 1, date("Y", $time_stamp));
				$gc = $this->purseTime($time_stamp);
				$res[] = $gc;
				$lim--;
			}
			return $res;
		}
        // 期間リスト
        $time_array = array();
        while ($lim != 0) {
            $time_stamp = mktime(0, 0, 0, date('m', $time_stamp), date('d', $time_stamp) + 1, date('Y', $time_stamp));
            $time_array[] = $time_stamp;
            $lim--;
        }
        // 旧暦
        $luna_array = $this->getLunaCalendarList($time_array, JD_KEY_TIMESTAMP);
        foreach ($time_array as $time_stamp) {
            $gc = $this->purseTime($time_stamp, $luna_array[$time_stamp]);
            $res[] = $gc;
        }
        return $res;
	}
	
	/**
	 * タイムスタンプを展開して、日付情報を返します
	 *
	 * @param int $time_stamp タイムスタンプ
	 * @param boolean $luna 旧暦情報を使用するかどうか(falseにすると、旧暦情報を取得しない代わりに、より高速に動作します)
	 * @return array
	 */
	function purseTime($time_stamp, $luna = true)
	{
		$holiday = $this->getHolidayList($time_stamp);

		$day = date('j', $time_stamp);
		$res = array(
			'time_stamp' => $time_stamp,
			'day'        => $day,
			'strday'     => date('d', $time_stamp),
			'holiday'    => isset($holiday[$day]) ? $holiday[$day] : JD_NO_HOLIDAY,
			'week'       => $this->getWeekday($time_stamp),
			'month'      => date('m', $time_stamp),
			'year'       => date('Y', $time_stamp),
		);
		
		if ($luna === true) {
			$luna = $this->getLunarCalendar($time_stamp);
		}
		
		if (is_array($luna)) {
			$res['sixweek']      = $this->getSixWeekday($luna['time_stamp']);
			$res['luna_sixweek'] = $luna['time_stamp'];
			$res['is_chuki']     = $luna['is_chuki'];
			$res['chuki']        = $luna['chuki'];
			$res['tuitachi_jd']  = $luna['tuitachi_jd'];
			$res['jd']           = $luna['jd'];
			$res['luna_year']    = $luna['year'];
			$res['luna_month']   = $luna['month'];
			$res['luna_day']     = $luna['day'];
			$res['uruu']         = $luna['uruu'];
		}
		return $res;
	}
	
	/**
	 * 旧暦・月齢を取得する
	 *
	 * @param int $time_stamp タイムスタンプ
	 * @see japaneseDate_lunarCalendar::getLunarCalendar()
	 * @return array
	 */
	function getLunarCalendar($time_stamp)
	{
		return $this->lc->getLunarCalendar($time_stamp);
	}
	
	/**
	 * 旧暦・月齢リストを取得する
	 *
	 * @param array $time_stamp_array タイムスタンプのリスト
	 * @param array $mode JD_KEY_TIMESTAMP|JD_KEY_ORDERD
	 * @see japaneseDate_lunarCalendar::getLunaCalendarList()
	 * @return array
	 */
	function getLunaCalendarList($time_stamp_array, $mode = JD_KEY_ORDERD)
	{
		return $this->lc->getLunaCalendarList($time_stamp_array, $mode);
	}
	
	/**
	 * ユニックスタイムスタンプから、ユリウス暦を取得します。
	 *
	 * @param int $time_stamp タイムスタンプ
	 * @see japaneseDate_lunarCalendar::time2JD()
	 * @return float
	 */
	function time2JD($time_stamp)
	{
		return $this->lc->time2JD($time_stamp);
	}
	
	
	/**
	 * 朔のリストを取得する
	 *
	 * @param int $time_stamp タイムスタンプ
	 * @see japaneseDate_lunarCalendar::getLunarCalendar()
	 * @return array
	 */
	function getTuitachiArray($time_stamp)
	{
		return $this->lc->getTuitachiArray($this->lc->getTime2JD($time_stamp));
	}
	
	
	/**
	 * 日本語カレンダー対応したstrftime()
	 *
	 * <pre>{@link http://php.five-foxes.com/module/php_man/index.php?web=function.strftime strftimeの仕様}
	 * に加え、
	 * %J 1～31の日
	 * %g 1～9なら先頭にスペースを付ける、1～31の日
	 * %K 和名曜日
	 * %k 六曜番号
	 * %6 六曜
	 * %K 曜日
	 * %l 祝日番号
	 * %L 祝日
	 * %o 干支番号
	 * %O 干支
	 * %N 1～12の月
	 * %E 旧暦年
	 * %G 旧暦の月
	 * %F 年号
	 * %f 年号ID
	 * 
	 * が使用できます。</pre>
	 *
	 * @since 1.1
	 * @param string $format フォーマット
	 * @param integer $time_stamp 変換したいタイムスタンプ(デフォルトは現在のロケール時間)
	 * @param boolean $luna 旧暦情報を使用するかどうか(falseにすると、旧暦情報を取得しない代わりに、より、高速に動作します)
	 * @
	 */
	function mb_strftime($format, $time_stamp = false, $luna = true)
	{
		if ($time_stamp === false) {
			$time_stamp = time();
		}
		$jtime = $this->purseTime($time_stamp, $luna);
		$OrientalZodiac = $this->getOrientalZodiac($time_stamp);
		$jd_token = array(
			'%o' => $OrientalZodiac,
			'%O' => $this->viewOrientalZodiac($OrientalZodiac),
			'%l' => $jtime['holiday'],
			'%L' => $this->viewHoliday($jtime['holiday']),
			'%K' => $this->viewWeekday($jtime['week']),
			'%k' => $luna ? $this->viewSixWeekday($jtime['sixweek']) : '',
			'%6' => $luna ? $jtime['sixweek'] : '',
			'%g' => strlen($jtime['day']) == 1 ? ' '.$jtime['day'] : $jtime['day'],
			'%J' => $jtime['day'],
			'%G' => $this->viewMonth($this->getMonth($time_stamp)),
			'%N' => $this->getMonth($time_stamp),
			'%F' => $this->viewEraName($this->getEraName($time_stamp)),
			'%f' => $this->getEraName($time_stamp),
			'%E' => $this->getEraYear($time_stamp)
		);

		$resstr = '';
		$format_array = explode('%', $format);
		$count = count($format_array)-1;
		$i = 0;
		while (isset($format_array[$i])) {
            if (($i == 0 || $i == $count) && $format_array[$i] == '') {
                $i++;
                continue;
            }

            if ($format_array[$i] == '') {
                $resstr .= '%%';
                $i++;
                if (isset($format_array[$i])) {
                    $resstr .= $format_array[$i];
                }
                $i++;
                continue;
            }

            $token = '%'.mb_substr($format_array[$i], 0, 1);
            if (isset($jd_token[$token])) {
                $token = $jd_token[$token];
            }
            if (mb_strlen($format_array[$i]) > 1) {
                $token .= mb_substr($format_array[$i], 1);
            }
            $resstr .= $token;
            $i++;
        }
		return strftime($resstr, $time_stamp);
	}
}
