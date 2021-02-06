<?php

class NP_Calendar extends NucleusPlugin
{
	function getName()
	{
		return 'Calendar Plugin';
	}

	function getAuthor()
	{
		return 'karma / roel / jhoover / admun / hcgtv / mimie / yama | others';
	}

	function getURL()
	{
		return 'http://nucleuscms.org/';
	}

	function getVersion()
	{
		return '1.00';
	}

	function getMinNucleusVersion()
	{
		return 350;
	}

	function supportsFeature($what)
	{
		switch ($what) {
			case 'SqlTablePrefix':
			case 'SqlApi':
				return 1;
			default:
				return 0;
		}
	}

	function getDescription()
	{
		return NP_CALENDAR_DESC;
	}

	function getEventList()
	{
		return array('PreSkinParse');
	}
	
	function install()
	{
		$this->createOption('Locale', NP_CALENDAR_LOCALE_LABEL, 'text', NP_CALENDAR_LOCALE_VALUE);
		$this->createOption('TimeFormat', NP_CALENDAR_TIME_FORMAT_LABEL, 'text', NP_CALENDAR_TIME_FORMAT_VALUE);
		$this->createOption('LinkAll', NP_CALENDAR_LINKALL_LABEL, 'yesno', 'no');
		$this->createOption('JustCal', NP_CALENDAR_JUSTCAL_LABEL, 'yesno', 'no');
		$this->createOption('Summary', NP_CALENDAR_SUMMARY_LABEL, 'text', NP_CALENDAR_SUMMARY_VALUE);
		$this->createOption('prevm', NP_CALENDAR_PREVM_LABEL, 'text', '&lt;');
		$this->createOption('nextm', NP_CALENDAR_NEXTM_LABEL, 'text', '&gt;');
		$this->createOption('delim', NP_CALENDAR_DELIM_LABEL, 'text', '&nbsp;');
		$this->createOption('wday_array', NP_CALENDAR_WDAY_LABEL, 'text', NP_CALENDAR_WDAY_VALUE);
		$this->createOption('startsun', NP_CALENDAR_STARTSUN_LABEL, 'yesno', 'yes');
		$this->createOption('special_day', NP_CALENDAR_SPECIAL_DAY, 'textarea', NP_CALENDAR_SPECIAL_DAY_VALUE);
		$this->createOption('include_css', NP_CALENDAR_OUTPUT_CSS, 'yesno', 'yes');
		$sample_css = file_get_contents($this->getDirectory() . 'sample.css');
		$this->createOption('css_value', NP_CALENDAR_OUTPUT_CSS_VALUE, 'textarea', $sample_css);
	}

	function init()
	{
		$language = str_replace(array('\\', '/'), '', getLanguageName());
		$langDir  = $this->getDirectory() . 'lang/';
		if (!@include(sprintf("%s%s.php", $langDir, $language))) {
			include_once($langDir . 'english.php');
		}
	}

	function event_PreSkinParse(&$data)
	{
		if ($this->getOption('include_css') !== 'yes') return;
		$contents = &$data['contents'];
		$css  = '<style type="text/css">' . "\n";
		$css .= '<!--' . "\n";
		$css .= $this->getOption('css_value');
		$css .= '-->' . "\n";
		$css .= '</style>' . "\n";
		$contents = str_replace('</head>', $css . '</head>', $contents);
	}

	function doSkinVar($skinType, $view = 'all', $blogName = '')
	{
		global $manager, $blog, $CONF, $archive, $itemid;
		$language = str_replace(array('\\', '/'), '', getLanguageName());
		switch ($language) {
			case 'japanese-utf8':
			case 'japanese-euc':
				require_once($this->getDirectory() . "libs/japaneseDate/japaneseDate.php");
				$useextlib = true;
				break;
			default:
				$useextlib = false;
		}

		if ($blogName) $b = &$manager->getBlog(getBlogIDFromName($blogName));
		elseif ($blog) $b = &$blog;
		else           $b = &$manager->getBlog($CONF['DefaultBlog']);

		/*
		* select which month to show
		* - for archives: use that month
		* - otherwise: use current month
		*/
		switch ($skinType) {
			case 'item':
				$item = &$manager->getItem($itemid, 0, 0);
				$time = $item['timestamp'];
				break;
			case 'archive':
				sscanf($archive, '%d-%d-%d', $y, $m, $d);
				$time = mktime(0, 0, 0, $m, 1, $y);
				break;
			default:
				$time = $b->getCorrectTime(time());
		}

		/* Set $category if $view = 'limited'
		* This means only items from the specified category
		* will be displayed in the calendar.
		* Defaults to show all categories in calendar.
		*/
		$category = ($view === 'limited') ? $blog->getSelectedCategory() : 0;
		$this->_drawCalendar($time, $b, $this->getOption('LinkAll'), $category, $useextlib);
	}

	/**
	 * This function draws the actual calendar as a table
	 */
	function _drawCalendar($timestamp, &$blog, $linkall, $category, $useextlib)
	{
		$blogid   = (int)$blog->getID();
		$tbl_item = sql_table('item');

		// set correct locale
		setlocale(LC_TIME, $this->getOption('Locale'));
		$time_format = $this->getOption('TimeFormat');

		// get year/month etc
		$date = getDate($timestamp);

		$month = $date['mon'];
		$year  = $date['year'];

		// get previous year-month
		$last_month = $month - 1;
		$last_year = $year;
		if (!checkdate($last_month, 1, $last_year)) {
			$last_month += 12;
			$last_year--;
		}

		if ($last_month < 10) {
			$last_month = "0" . $last_month;
		} else {
			$last_month >= 10;
			$last_month = $last_month;
		}

		// get the next year-month
		$next_month = $month + 1;
		$next_year = $year;
		if (!checkdate($next_month, 1, $next_year)) {
			$next_year++;
			$next_month -= 12;
		}

		if ($next_month < 10) {
			$next_month = "0" . $next_month;
		} else {
			$next_month >= 10;
			$next_month = $next_month;
		}

		$nolink = $this->getOption('JustCal');

		// find out for which days we have posts
		if ($linkall === 'no' && $nolink === 'no') {
			$days = array();
			$timeNow = $blog->getCorrectTime();
			if ($category != 0) {
				$res = sql_query("SELECT DAYOFMONTH(itime) as day FROM {$tbl_item} WHERE icat={$category} and MONTH(itime)={$month} and YEAR(itime)={$year} and iblog={$blogid} and idraft=0 and UNIX_TIMESTAMP(itime)<{$timeNow} GROUP BY day");
			} else {
				$res = sql_query("SELECT DAYOFMONTH(itime) as day FROM {$tbl_item} WHERE MONTH(itime)={$month} and YEAR(itime)={$year} and iblog={$blogid} and idraft=0 and UNIX_TIMESTAMP(itime)<{$timeNow} GROUP BY day");
			}

			while ($o = sql_fetch_object($res)) {
				$days[$o->day] = 1;
			}
		}

		$prev  = $this->getOption('prevm');
		$next  = $this->getOption('nextm');
		$delim = $this->getOption('delim');

		// draw header
		$currentdate = getDate();
		if ($next_month > $currentdate['mon'] && $year == $currentdate['year']) {
			$future = false;
		} else {
			if ($next_month < $currentdate['mon'] && $next_year > $currentdate['year']) {
				$future = false;
			} else {
				$future = true;
			}
		}

		$oldestdate = explode('-', quickquery("SELECT SUBSTR(itime,1,7) as result FROM {$tbl_item} WHERE iblog= {$blogid} AND idraft=0 ORDER BY itime ASC LIMIT 1"));
		if (
			$last_month < $oldestdate[1]
			&& $year == $oldestdate[0]
		)          $past = false;
		else {
			if (
				$last_month > $oldestdate[1]
				&& $last_year < $oldestdate[0]
			)  $past = false;
			else                              $past = true;
		}

		if ($nolink === "yes") {
			$str = "<!-- calendar start -->\n";
			$str .= '<table class="calendar" summary="' . $this->hsc($this->getOption('Summary')) . '">';
			$str .= "<caption>\n";
			$str .= strftime($time_format, $timestamp);
			$str .= "</caption>\n";
			$str .= '<tr class="calendardateheaders">' . "\n";
		} else {
			$str = "<!-- calendar start -->\n";
			$str .= '<table class="calendar" summary="' . $this->hsc($this->getOption('Summary')) . '">';
			$str .= "<caption>\n";
			if ($past) {
				$str .= '<a href="' . createArchiveLink($blogid, $last_year . '-' . $last_month) . '">' .  "{$prev}</a>\n";
			} else {
				// No link to past
				$str .= $prev;
			}
			$str .= $delim;
			$str .= '<a href="' . createArchiveLink($blogid, strftime('%Y-%m', $timestamp)) . '">' . strftime($time_format, $timestamp) . '</a>' . "\n";
			$str .= $delim;
			if ($future) {
				$str .= '<a href="' . createArchiveLink($blogid, $next_year . '-' . $next_month) . '">' . "{$next}</a>\n";
			} else {
				// No link to future
				$str .= $next;
			}
			$str .= "</caption>\n";
			$str .= '<tr class="calendardateheaders">' . "\n";
		}

		$startsun = $this->getOption('startsun');
		// output localized weekday-abbreviations as column headers
		if ($startsun === 'yes') {
			$daylabel = explode(',', $this->getOption('wday_array'));

			foreach ($daylabel as $weekday) {
				$str .= "<th>{$weekday}</th>";
			}
			$str .= "</tr>\n";
			$str .= '<tr>';

			// draw empty cells for all days before start
			$firstDay = getDate(mktime(0, 0, 0, $month, 1, $year));

			if ($startsun === 'yes') {
				$wday = 1;
				while ($wday <= $firstDay['wday']) {
					$wday++;
					$str .= '<td class="blank"><div>&nbsp;</div></td>';
				}
			} else {
				if ($firstDay['wday'] == 0) $firstDay['wday'] = 7;

				$wday = 1;
				while ($wday < $firstDay['wday']) {
					$wday++;
					$str .= '<td class="blank"><div>&nbsp;</div></td>';
				}
			}
			$special_day = $this->read_days_list($this->getOption('special_day'));
			if ($useextlib) {
				$jd = new japaneseDate();
				$holiday = array_keys($jd->getHolidayList($timestamp));
			} else {
				$holiday = array(0);
			}

			$mday = 1;
			$to_day = date('j', $blog->getCorrectTime());
			$this_month = date('n');
			$this_year = date('Y');
			while (checkdate($month, $mday, $year)) {
				if (in_array($mday, $special_day)) {
                    $class = 'special';
                } elseif ($mday == $to_day && $this_month == $month && $this_year == $year) {
                    $class = 'today';
                } elseif (in_array($mday, $holiday)) {
                    $class = 'holiday';
                } elseif (($startsun === 'yes' && $wday == 1) || ($startsun !== 'yes' && $wday == 7)) {
                    $class = 'sunday';
                }
				elseif (($startsun === 'yes' && $wday == 7) || ($startsun !== 'yes' && $wday == 6)) {
                    $class = 'saturday';
                } else {
                    $class = 'days';
                }
				$str .= '<td class="' . $class . '">';

				if (($linkall === 'yes' && $nolink === 'no') || $days[$mday]) {
					$str .= '<a href="' . createArchiveLink($blogid, $year . '-' . $month . '-' . $mday) . '">' . "{$mday}</a></td>";
				} else {
					$str .= "<div>{$mday}</div></td>";
				}

				$mday++;
				$wday++;
				if (($wday > 7) && (checkdate($month, $mday, $year))) {
					$str .= "</tr>\n";
					$str .= '<tr>';
					$wday = 1;
				}
			}

			while ($wday++ < 8) {
				$str .= '<td class="blank"><div>&nbsp;</div></td>';
			}
			$str .= "</tr>\n";

			// footer
			$str .= '</table>';
			$str .= "\n<!-- calendar end -->\n";
			echo $str;
		}
	}

	function read_days_list($data)
	{
		$init_array = explode("\n", $data);
		$result = array();
		foreach ($init_array as $str) {
			$str =  trim(preg_replace('@\s@', '', $str));
			if (preg_match('@^[/#*]@', $str)) {
                continue;
            }
			list($year, $days) = explode(':', $str);
			if (date('Y') !== $year) {
                continue;
            }
			$days_array = explode(',', $days);
			foreach ($days_array as $value) {
				$needle = '@^' . date('m') . '@';
				if (strpos($value, '01') === 0) {
					$result[] = substr($value, 2);
				}
			}
		}
		return $result;
	}

	function hsc($str)
	{
		if (!function_exists('hsc')) {
            return htmlspecialchars($str);
        }
        return hsc($str);
    }
}
