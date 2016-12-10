<?php

/**
 * Provide a calendar block
 * @author Yahav Gindi Bar
 * @copyright Yahav Gindi Bar
 * @since San, 12/03/2011 02:15:36 GMT
 */
class PearAddon_CalendarBlock extends PearAddon
{
	/**
	 * Addon UUID
	 * @var String
	 */
	var $addonUUID				=	"4f78d27a-c318-4ae5-8ef9-14e39a1c4e4b";
	
	/**
	 * The addon name
	 * @var String
	 */
	var $addonName				=	"Calendar Block";
	
	/**
	 * The addon description
	 * @var String
	 */
	var $addonDescription		=	"Add calendar block to the blocks list.";
	
	/**
	 * The addon author
	 * @var String
	 */
	var $addonAuthor				=	"Yahav Gindi Bar";
	
	/**
	 * The addon author website
	 * @var String
	 */
	var $addonAuthorWebsite		=	"http://yahavgindibar.com";
	
	/**
	 * The addon version
	 * @var String
	 */
	var $addonVersion			=	"1.0.0";
	
	/**
	 * Array contains today date formatted with DST support
	 * @var Array
	 */
	var $todayDate				=	array();
	
	/**
	 * The calendar chosen month
	 * @var Integer
	 */
	var $chosenMonth			=	0;
	
	/**
	 * The calendar chosen year
	 * @var Integer
	 */
	var $chosenYear			=	0;
	
	/**
	 * The selected date timestamp
	 * @var Integer
	 */
	var $selectedDatestamp	=	0;
	
	/**
	 * Array contains the first day data
	 * @var Array
	 */
	var $firstDayData		=	array();
	
	/**
	 * Array contains the localized monthes names
	 * @var Array
	 */
	var $localizedMonthes	=	array();
	
	/**
	 * Array contains the localized days names
	 * @var Array
	 */
	var $localizedDays		=	array();
	
	/**
	 * Initialize the addon. this function is called each time the addon is being used, as a module, or as an action.
	 * Used to register events, load classes etc.
	 * @return Void
	 */
	function initialize()
	{
		if ( parent::initialize() )
		{
			$this->pearRegistry->loadLibrary('PearBlocksManager', 'blocks_manager');
			$this->pearRegistry->loadedLibraries['blocks_manager']->registerBlockType('calendar', 'Calendar', 'Create a calendar block', $this);
		
			return true;
		}
		
		return false;
	}
	
	function getSiteActions()
	{
		return array(
				'calendar'			=>	array('calendar', 'Calendar', 'Calendar',)
		);
	}
	
	/**
	 * Get the block content
	 * @param Array $blockData
	 * @return String
	 */
	function getBlockContent( $blockData )
	{
		//------------------------------------------------
		//	Init
		//------------------------------------------------
		
		$this->__setupCalendar();
		
		//------------------------------------------------
		//	Get next and prev dates
		//------------------------------------------------
		
		$nextMonth			=	$this->__getNextMonth($this->chosenYear, $this->chosenMonth);
		$prevMonth			=	$this->__getPreviousMonth($this->chosenYear, $this->chosenMonth);
		
		//------------------------------------------------
		//	Return the template
		//------------------------------------------------
		
		return $this->pearRegistry->response->loadedViews['global']->render('sidebarBlock', array(
			'blockName'				=>	$blockData['block_name'] . ': ' . $this->localizedMonthes[ $this->chosenMonth - 1 ] . ', ' . $this->chosenYear,
			'blockContent'			=>	$this->getCalendarBlockTemplate($this->chosenMonth, $this->chosenYear, $prevMonth, $nextMonth)
		));
	}
	
	function getCalendarBlockTemplate($month, $year, $prev, $next)
	{
		//------------------------------------------------
		//	Init
		//------------------------------------------------
		$html					=	'<div class="calendar-block">'
								.	'<div class="navigation-links">'
								.	'<a href="' . $this->pearRegistry->baseUrl . 'index.php?cal_year=' . $prev['year'] . '&amp;cal_mon=' . $prev['mon'] . '" class="float_left">&rarr; ' . $prev['name'] . ', ' . $prev['year'] . '</a>'
								.	'<a href="' . $this->pearRegistry->baseUrl . 'index.php?cal_year=' . $next['year'] . '&amp;cal_mon=' . $next['mon'] . '" class="float_right">' . $next['name'] . ', ' . $next['year'] . ' &larr;</a>'
								.	'<div class="clear"></div>'
								.	'</div><table>';
		$seenDays				=	array();
		$seenIds					=	array();
		$currentDay				=	array();
		$iterYear				=	'';
		$iterMonth				=	'';
		$iterDay					=	'';
		$checkDate				=	0;
		$weeksOutput				=	array();
		$daysOutput				=	'';
		
		
		//------------------------------------------------
		//	Print the days as localized strings
		//------------------------------------------------
		$html					.=	'<tr class="table-header">';
		
		foreach ( $this->localizedDays as $day )
		{
			$html				.=	'<td>' . $this->pearRegistry->mbSubstr($day, 0, 3) . '</td>';
		}
		
		//------------------------------------------------
		//	Fetch the first day of the month
		//------------------------------------------------
		$dateTimestamp			=	gmmktime(0, 0, 0, $month, 1, $year);
		$firstDayOfMonth			=	$this->pearRegistry->gmDate($dateTimestamp);
		
		$html					.=	'</tr>';
		
		if ( ! $this->pearRegistry->localization->selectedLanguage['language_calendar_week_from_sunday'] )
		{
	        $firstDayOfMonth['wday'] = ($firstDayOfMonth['wday'] == 0 ? 7 : $firstDayOfMonth['wday']);
        }
		
        //------------------------------------------------
        //	Iterate and start
        //------------------------------------------------
        
        for ( $c = 0 ; $c < 42; $c++ )
        {
        		//------------------------------------------------
        		//	Get the current iteration day
        		//------------------------------------------------
        	
       	 	$iterYear				= gmdate('Y', $dateTimestamp);
       	 	$iterMonth				= gmdate('n', $dateTimestamp);
        		$iterDay					= gmdate('j', $dateTimestamp);
        		$currentDay				= $this->pearRegistry->gmDate($dateTimestamp);
        		$checkDate				= $c;
        	
        		//------------------------------------------------
        		//	Are we using sunday as the week first day?
        		//------------------------------------------------
        		if ( ! $this->pearRegistry->localization->selectedLanguage['language_calendar_week_from_sunday'] )
			{
	        		$checkDate			= ($c + 1);
	        	}
	        
	        	if ( ($c % 7) == 0 )
	        	{
	        		//------------------------------------------------
	        		//	We're starting new week, this week is out of the month range?
	        		//------------------------------------------------
	        		if ($currentDay['mon'] != $month)
	        		{
	        			//------------------------------------------------
	        			//	Make sure to append the last month content
	        			//------------------------------------------------
	        			if ( ! empty($daysOutput) )
	        			{
	        				$weeksOutput[] = $daysOutput;
	        			}
	        			
	        			break;
	        		}
	        		
	        		//------------------------------------------------
	        		//	Append and clean buffers
	        		//------------------------------------------------
	        		$weeksOutput[] = $daysOutput;
	        		$daysOutput = '';
	        	}
	        	
	        	//------------------------------------------------
	        	//	The spot of day is included in this month first week?
	        	//	(e.g. 01/01/2011 is Thursday, so the first week Sunday (or Monday, depending on the week starting day) to Thurday is blank spots.
	        	//	the 11/30/2011 was Wensday, this the end of the month, but the table continues until Sut or San, depending on Monday as the first day, so we have to mark them as blank spots.)
	        	//------------------------------------------------
	        	if ( ($checkDate < $currentDay['wday']) or ($currentDay['mon'] != $month) )
	        	{
	        		$daysOutput .= '<td class="blank"></td>';
	        	}
	        	else
	        	{
	        		//------------------------------------------------
	        		//	We've seen that day before?
	        		//------------------------------------------------
	        		if (in_array($currentDay['yday'], $seenDays) )
	        		{
	        			continue;
	        		}
	        		
	        		$seenDays[] = $currentDay['yday'];
	        		
	        		//------------------------------------------------
	        		//	Today's date?
	        		//------------------------------------------------
	        		if ( ($currentDay['mday'] == $this->todayDate['mday']) and ($this->todayDate['mon'] == $currentDay['mon']) and ($this->todayDate['year'] == $currentDay['year']))
	        		{
	        			$daysOutput .= '<td class="row' . ($c % 2 == 0 ? '1' : '2') . ' today">' . $currentDay['mday'] . '</td>';
	        		}
	        		else
	        		{
	        			$daysOutput .= '<td class="row' . ($c % 2 == 0 ? '1' : '2' ) . '">' . $currentDay['mday'] . '</td>';
	        		}
        			
	        		//------------------------------------------------
	        		//	Move forward by day
	        		//------------------------------------------------
	        		$dateTimestamp += 86400;
	        	}
        }
        
        $html					.=	'<tr>' . implode('</tr><tr>', $weeksOutput) . '</tr>';
		$html					.=	'</table>' . PHP_EOL . '</div>';
		return $html;
	}
	
	/**
	 * Setup the calendar initialize vars
	 * @return Void
	 */
	function __setupCalendar()
	{
		//------------------------------------------------
		//	Filter input vars
		//------------------------------------------------
		
		$this->pearRegistry->request['cal_year']				=	intval($this->pearRegistry->request['cal_year']);
		$this->pearRegistry->request['cal_mon']				=	intval($this->pearRegistry->request['cal_mon']);
		
		//------------------------------------------------
		//	Get dates
		//------------------------------------------------
		
		/** This handrolled line take into account DST when gmdate alone refuses. **/
		
		$explodedDate		= explode( ',', gmdate( 'Y,n,j,G,i,s', time() + $this->pearRegistry->getTimeOffset() ) );
		
		$this->todayDate		= array
		(
				'year'			=> intval($explodedDate[0]),
				'mon'			=> intval($explodedDate[1]),
				'mday'			=> intval($explodedDate[2]),
				'hours'			=> intval($explodedDate[3]),
				'minutes'		=> intval($explodedDate[4]),
				'seconds'		=> intval($explodedDate[5])
		);
		
		//------------------------------------------------
		//	Fetch the chosen dates
		//------------------------------------------------
		
		$this->chosenYear			=	( $this->pearRegistry->request['cal_year'] > 0 ? $this->pearRegistry->request['cal_year'] : $this->todayDate['year'] );
		$this->chosenMonth			=	( $this->pearRegistry->request['cal_mon'] > 0 ? $this->pearRegistry->request['cal_mon'] : $this->todayDate['mon'] );
		
		//------------------------------------------------
		//	Make sure the date is valid
		//------------------------------------------------
		
		if (! checkdate($this->chosenMonth, 1, $this->chosenYear) )
		{
			$this->chosenMonth			=	$this->todayDate['mon'];
			$this->chosenYear			=	$this->todayDate['year'];
		}
		
		//------------------------------------------------
		//	Set-up
		//------------------------------------------------
		
		$this->selectedDatestamp			= mktime(0, 0, 1, $this->chosenMonth, 1, $this->chosenYear);
		$this->firstDayData				= $this->pearRegistry->gmDate($this->selectedDatestamp);
		$this->localizedMonthes			= array( $this->pearRegistry->localization->lang['mon_1'], $this->pearRegistry->localization->lang['mon_2'], $this->pearRegistry->localization->lang['mon_3'],
				$this->pearRegistry->localization->lang['mon_4'], $this->pearRegistry->localization->lang['mon_5'], $this->pearRegistry->localization->lang['mon_6'],
				$this->pearRegistry->localization->lang['mon_7'], $this->pearRegistry->localization->lang['mon_8'], $this->pearRegistry->localization->lang['mon_9'],
				$this->pearRegistry->localization->lang['mon_10'], $this->pearRegistry->localization->lang['mon_11'], $this->pearRegistry->localization->lang['mon_12']);
		
		if ( $this->pearRegistry->localization->selectedLanguage['language_calendar_week_from_sunday'] )
		{
			$this->localizedDays			= array( $this->pearRegistry->localization->lang['day_7'], $this->pearRegistry->localization->lang['day_1'], $this->pearRegistry->localization->lang['day_2'],
					$this->pearRegistry->localization->lang['day_3'], $this->pearRegistry->localization->lang['day_4'], $this->pearRegistry->localization->lang['day_5'],
					$this->pearRegistry->localization->lang['day_6'] );
		}
		else
		{
			$this->localizedDays			= array( $this->pearRegistry->localization->lang['day_1'], $this->pearRegistry->localization->lang['day_2'], $this->pearRegistry->localization->lang['day_3'],
					$this->pearRegistry->localization->lang['day_4'], $this->pearRegistry->localization->lang['day_5'], $this->pearRegistry->localization->lang['day_6'],
					$this->pearRegistry->localization->lang['day_7'] );
		}
	}
	
	/**
	 * Get the next month for specific month
	 * @param Integer $year
	 * @param Integer $month
	 * @return Integer
	 * @access Private
	 */
	function __getNextMonth($year, $month)
	{
		//------------------------------------------------
		//	This is the last month?
		//------------------------------------------------
	
		if ( $month === 12 )
		{
			return array(
					'mon'		=>	1,
					'year'		=>	( $year + 1 ),
					'name'		=>	$this->localizedMonthes[ 0 ]
			);
		}
	
		//------------------------------------------------
		//	Return default cases
		//------------------------------------------------
		return array(
				'mon'		=>	( $month + 1 ),
				'year'		=>	$year,
				'name'		=>	$this->localizedMonthes[ $month ]
		);
	}
	
	/**
	 * Get the previous month for specific month
	 * @param Integer $year
	 * @param Integer $month
	 * @return Integer
	 * @access Private
	 */
	function __getPreviousMonth($year, $month)
	{
		//------------------------------------------------
		//	This is the first month?
		//------------------------------------------------
		
		if ( $month === 1 )
		{
			return array(
				'mon'		=>	12,
				'year'		=>	( $year - 1 ),
				'name'		=>	$this->localizedMonthes[ 11 ]
			);
		}
		
		//------------------------------------------------
		//	Return default cases
		//------------------------------------------------
		return array(
				'mon'		=>	( $month - 1 ),
				'year'		=>	$year,
				'name'		=>	$this->localizedMonthes[ $month - 2 ]
		);
	}
	
	function ajaxMonthSwiping()
	{
		$this->pearRegistry->loadLibrary('PearAJAXRequest', 'ajax_manager');
		$this->pearRegistry->loadedLibraries['ajax_manager']->siteCharset = $this->pearRegistry->settings['site_charset'];
		
	}
}