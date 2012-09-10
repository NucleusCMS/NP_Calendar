<?php

/**
  * This plugin can be used to insert a calendar on your page
  *
  * History:
  *   v0.87: synchronization of item date (by yama)
  *   v0.861: multi language (by yama)
  *   v0.86: for japanese (by mimie)
  *   v0.85: added year validation for "today" td (by rayrizzo)
  *   v0.84: add delim between prev/next month (by cs42)
  *   v0.83: no link to future month
  *   v0.82: fixed blogname parameter bug reported by Pieter
  *   v0.81b: fixed bug reported by Pieter
  *   v0.81a: typo
  *   v0.81: included hcgtv's improvement
  *   v0.80: fixed today highlight bug
  *   v0.79a: disabled prev/next month link if SC mode is enabled
  *   v0.79: fixed typo
  *   v0.78a: merged gRegor's category change
  *   v0.78: added straight calendar mode (no links)
  *   v0.77: added option to change prev/next month link label
  *          added option to change day label
  *          added option to start week on Sun (integrated changes from Hop)
  *   v0.76: use sql_table, add supportsFeature, add today highlight, merged w/ XE
  *   v0.75: short_open_tags: <? -> <?php (karma)
  *   v0.73: jhoover fixed the bug that appeared in the monthly archives
  *      (showing December 1999 everywhere) when using Fancy URL's
  *   v0.72: links to previous/next month added (by Roel)
  *      Fixed: "Passing locale category name as string is deprecated" by removing two quotes ;-)
  *   v0.71: last table cell was missing
  *   v0.7: dates with future/draft posts are no longer linked
  *   v0.6: using options now instead of instance variables to keep options
  *   v0.51: fixed: calender was not limited to blog
  *   v0.5: - moved table summary to constructor
  *         - date headers now created according to locale
  *   v0.21: added doTemplateVar
  *   v0.2: initial plugin
  *
  */

if (!function_exists('sql_table')) {
  function sql_table($name) {
    return 'nucleus_' . $name;
  }
}

class NP_Calendar extends NucleusPlugin {

  /**
   * Plugin data to be shown on the plugin list
   */
  function getName() { return 'Calendar Plugin'; }
  function getAuthor() { return 'karma / roel / jhoover / admun / hcgtv / mimie / yama | others'; }
  function getURL() { return 'http://nucleuscms.org/'; }
  function getVersion() { return '0.87'; }
  function getDescription() {
    return 'This plugin can be called from within skins to insert a calender on your site, by using &lt;%Calendar%&gt;.';
  }

  function supportsFeature($feature) {
    switch($feature) {
      case 'SqlTablePrefix':
        return 1;
      case 'HelpPage':
        return 0;
      default:
        return 0;
    }
  }

  /**
   * On plugin install, three options are created
   */
  function install() {
    // create some options
    $this->createOption('Locale',NP_CALENDAR_LOCALE_LABEL,'text',NP_CALENDAR_LOCALE_VALUE);
    $this->createOption('TimeFormat',NP_CALENDAR_TIME_FORMAT_LABEL,'text',NP_CALENDAR_TIME_FORMAT_VALUE);
    $this->createOption('LinkAll',NP_CALENDAR_LINKALL_LABEL,'yesno','no');
    $this->createOption('JustCal',NP_CALENDAR_JUSTCAL_LABEL,'yesno','no');
    $this->createOption('Summary',NP_CALENDAR_SUMMARY_LABEL,'text',NP_CALENDAR_SUMMARY_VALUE);
    $this->createOption('prevm',NP_CALENDAR_PREVM_LABEL,'text','&lt;');
    $this->createOption('nextm',NP_CALENDAR_NEXTM_LABEL,'text','&gt;');
    $this->createOption('delim',NP_CALENDAR_DELIM_LABEL,'text','&nbsp;');
    $this->createOption('mon',NP_CALENDAR_MON_LABEL,'text',NP_CALENDAR_MON_VALUE);
    $this->createOption('tue',NP_CALENDAR_TUE_LABEL,'text',NP_CALENDAR_TUE_VALUE);
    $this->createOption('wed',NP_CALENDAR_WED_LABEL,'text',NP_CALENDAR_WED_VALUE);
    $this->createOption('thu',NP_CALENDAR_THU_LABEL,'text',NP_CALENDAR_THU_VALUE);
    $this->createOption('fri',NP_CALENDAR_FRI_LABEL,'text',NP_CALENDAR_FRI_VALUE);
    $this->createOption('sat',NP_CALENDAR_SAT_LABEL,'text',NP_CALENDAR_SAT_VALUE);
    $this->createOption('sun',NP_CALENDAR_SUN_LABEL,'text',NP_CALENDAR_SUN_VALUE);
    $this->createOption('startsun',NP_CALENDAR_STARTSUN_LABEL,'yesno','yes');
  }


  /**
   * initialize:
   */
   function init() { 
      // include language file for this plugin 
      $language = str_replace(array('\\','/'), '', getLanguageName()); 
      if (file_exists($this->getDirectory()."lang/".$language.'.php')) 
         include_once($this->getDirectory()."lang/".$language.'.php'); 
      else 
         include_once($this->getDirectory()."lang/".'english.php'); 
   }


  /**
   * skinvar parameters:
   *      - blogname (optional)
   */
  function doSkinVar($skinType, $view = 'all', $blogName = '') {
    global $manager, $blog, $CONF, $archive, $itemid;

    /*
     * find out which blog to use:
     * 1. try the blog chosen in skinvar parameter
     * 2. try to use the currently selected blog
     * 3. use the default blog
     */
    if ($blogName) {
      $b =& $manager->getBlog(getBlogIDFromName($blogName));
    } else if ($blog) {
      $b =& $blog;
    } else {
      $b =& $manager->getBlog($CONF['DefaultBlog']);
    }

    /*
     * select which month to show
     * - for archives: use that month
     * - otherwise: use current month
     */
    switch($skinType) {
      case 'item':
      $item =& $manager->getItem($itemid,0,0);
        $time = $item[timestamp];
        break;
      case 'archive':
        sscanf($archive,'%d-%d-%d',$y,$m,$d);
        $time = mktime(0,0,0,$m,1,$y);
        break;
      default:
        $time = $b->getCorrectTime(time());
    }

    /* Set $category if $view = 'limited'
     * This means only items from the specified category
     * will be displayed in the calendar.
     * Defaults to show all categories in calendar.
     */
    $category = ($view == 'limited') ? $blog->getSelectedCategory() : 0;

    $this->_drawCalendar($time, $b, $this->getOption('LinkAll'), $category);
  }

   /**
    * This function draws the actual calendar as a table
    */
  function _drawCalendar($timestamp, &$blog, $linkall, $category) {
    $blogid = $blog->getID();

    // set correct locale
    setlocale(LC_TIME,$this->getOption('Locale'));
    $time_format = $this->getOption('TimeFormat');

    // get year/month etc
    $date = getDate($timestamp);

    $month = $date['mon'];
    $year = $date['year'];

    // get previous year-month
    $last_month = $month - 1;
    $last_year = $year;
    if (!checkdate($last_month, 1, $last_year)) {
      $last_month += 12;
      $last_year --;
    }

    if ($last_month < 10) {
      $last_month = "0".$last_month;
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
      $next_month = "0".$next_month;
    } else {
      $next_month >= 10;
      $next_month = $next_month;
    }

    $nolink = $this->getOption('JustCal');

    // find out for which days we have posts
    if ($linkall == 'no' && $nolink == 'no' ) {
      $days = array();
      $timeNow = $blog->getCorrectTime();
      if ($category != 0) {
        $res = sql_query('SELECT DAYOFMONTH(itime) as day FROM '.sql_table('item').' WHERE icat='.$category.' and MONTH(itime)='.$month.' and YEAR(itime)='.$year .' and iblog=' . $blogid . ' and idraft=0 and UNIX_TIMESTAMP(itime)<'.$timeNow.' GROUP BY day');
      } else {
        $res = sql_query('SELECT DAYOFMONTH(itime) as day FROM '.sql_table('item').' WHERE MONTH(itime)='.$month.' and YEAR(itime)='.$year .' and iblog=' . $blogid . ' and idraft=0 and UNIX_TIMESTAMP(itime)<'.$timeNow.' GROUP BY day');
      }

      while ($o = mysql_fetch_object($res)) {
        $days[$o->day] = 1;
      }
    }

    $prev = $this->getOption('prevm');
    $next = $this->getOption('nextm');
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

    if ($nolink == "yes") {
    ?> <!-- calendar start -->
      <table class="calendar" summary="<?php echo htmlspecialchars($this->getOption('Summary'))?>">
      <caption>
      <?php echo strftime($time_format,$timestamp); ?>
      </caption>
      <tr class="calendardateheaders">
    <?php
    } else {
    ?> <!-- calendar start -->
      <table class="calendar" summary="<?php echo htmlspecialchars($this->getOption('Summary'))?>">
      <caption>
      <a href="<?php echo createArchiveLink($blogid,$last_year.'-'.$last_month)?>"><?php echo $prev; ?></a>
      <?php echo $delim; ?>
      <a href="<?php echo createArchiveLink($blogid, strftime('%Y-%m',$timestamp))?>"><?php echo strftime($time_format,$timestamp)?></a>
      <?php echo $delim; ?>
    <?php
      if ($future) {
    ?>
      <a href="<?php echo createArchiveLink($blogid,$next_year.'-'.$next_month)?>"><?php echo $next?></a>
    <?php
      } else {
      // No link to future
      echo $next;
      }
    ?>
      </caption>
      <tr class="calendardateheaders">
    <?php
    }

    $startsun = $this->getOption('startsun');
    // output localized weekday-abbreviations as column headers
    if ($startsun == 'yes') {
      $daylabel = array(
        $this->getOption('sun'),
        $this->getOption('mon'),
        $this->getOption('tue'),
        $this->getOption('wed'),
        $this->getOption('thu'),
        $this->getOption('fri'),
        $this->getOption('sat'));
    } else {
      $daylabel = array(
        $this->getOption('mon'),
        $this->getOption('tue'),
        $this->getOption('wed'),
        $this->getOption('thu'),
        $this->getOption('fri'),
        $this->getOption('sat'),
        $this->getOption('sun'));
    }

    foreach($daylabel as $weekday) {
      echo '<th>' . $weekday . '</th>';
    }
    ?>
      </tr>
      <tr>
    <?php
    // draw empty cells for all days before start
    $firstDay = getDate(mktime(0,0,0,$month,1,$year));

    if ($startsun == 'yes') {
      $wday = 1;
      while ($wday <= $firstDay['wday']) {
        $wday++;
        echo '<td class="blank">&nbsp;</td>';
      }
    } else {
      if ($firstDay['wday'] == 0)
        $firstDay['wday'] = 7;

      $wday = 1;
      while ($wday < $firstDay['wday']) {
        $wday++;
        echo '<td class="blank">&nbsp;</td>';
      }
    }

    $mday = 1;
    $to_day = date("j", $blog->getCorrectTime());
    $this_month = date("n");
    $this_year = date("Y");
    while (checkdate($month, $mday, $year)) {
      if ($mday == $to_day && $this_month == $month && $this_year == $year) {
        echo '<td class="today">';
      } elseif($wday == 1) {
			echo '<td class="sunday">';
      } elseif($wday == 7) {
			echo '<td class="saturday">';
      } else {
			echo '<td class="days">';
      }

      if (($linkall == 'yes' && $nolink== 'no') || $days[$mday]) {
        echo '<a href="' . createArchiveLink($blogid,$year.'-'.$month.'-'.$mday) . '">' . $mday . '</a></td>';
      } else {
        echo $mday,'</td>';
      }

      $mday++; $wday++;
      if (($wday > 7) && (checkdate($month, $mday, $year))) {
        echo '</tr><tr>';
        $wday = 1;
      }
    }

    while ($wday++ < 8) {
      echo '<td class="blank">&nbsp;</td>';
    }
    echo '</tr>';

    // footer
    echo '</table>';
    echo "\n<!-- calendar end -->\n";
  }
}
?>