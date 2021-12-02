<?php
class Duong2amlich
{
	public function INT( $d )
	{
		return floor( $d ) ;
	}
 
	public function jdFromDate( $dd, $mm, $yy )
	{
		$a = $this::INT( ( 14 - $mm ) / 12 ) ;
		$y = $yy + 4800 - $a ;
		$m = $mm + 12 * $a - 3 ;
		$jd = $dd + $this::INT( ( 153 * $m + 2 ) / 5 ) + 365 * $y + $this::INT( $y / 4 ) - $this::INT( $y /
						100 ) + $this::INT( $y / 400 ) - 32045 ;
		if ( $jd < 2299161 )
		{
						$jd = $dd + $this::INT( ( 153 * $m + 2 ) / 5 ) + 365 * $y + $this::INT( $y / 4 ) -
										32083 ;
		}
		return $jd ;
	}
 
	public function jdToDate( $jd )
	{
		if ( $jd > 2299160 )
		{ // After 5/10/1582, Gregorian calendar
						$a = $jd + 32044 ;
						$b = $this::INT( ( 4 * $a + 3 ) / 146097 ) ;
						$c = $a - $this::INT( ( $b * 146097 ) / 4 ) ;
		}
		else
		{
						$b = 0 ;
						$c = $jd + 32082 ;
		}
		$d = $this::INT( ( 4 * $c + 3 ) / 1461 ) ;
		$e = $c - $this::INT( ( 1461 * $d ) / 4 ) ;
		$m = $this::INT( ( 5 * $e + 2 ) / 153 ) ;
		$day = $e - $this::INT( ( 153 * $m + 2 ) / 5 ) + 1 ;
		$month = $m + 3 - 12 * $this::INT( $m / 10 ) ;
		$year = $b * 100 + $d - 4800 + $this::INT( $m / 10 ) ;
		//echo "day = $day, month = $month, year = $year\n";
		return array(
						$day,
						$month,
						$year
					);
	}
 
	public function getNewMoonDay( $k, $timeZone )
	{
		$T = $k / 1236.85; // Time in Julian centuries from 1900 January 0.5
		$T2 = $T * $T;
		$T3 = $T2 * $T;
		$dr = M_PI / 180;
		$Jd1 = 2415020.75933 + 29.53058868 * $k + 0.0001178 * $T2 - 0.000000155 * $T3;
		$Jd1 = $Jd1 + 0.00033 * sin( ( 166.56 + 132.87 * $T - 0.009173 * $T2 ) * $dr); // Mean new moon
		$M = 359.2242 + 29.10535608 * $k - 0.0000333 * $T2 - 0.00000347 * $T3; // Sun's mean anomaly
		$Mpr = 306.0253 + 385.81691806 * $k + 0.0107306 * $T2 + 0.00001236 * $T3; // Moon's mean anomaly
		$F = 21.2964 + 390.67050646 * $k - 0.0016528 * $T2 - 0.00000239 * $T3; // Moon's argument of latitude
		$C1 = ( 0.1734 - 0.000393 * $T ) * sin( $M * $dr ) + 0.0021 * sin( 2 * $dr * $M );
		$C1 = $C1 - 0.4068 * sin( $Mpr * $dr ) + 0.0161 * sin( $dr * 2 * $Mpr);
		$C1 = $C1 - 0.0004 * sin( $dr * 3 * $Mpr);
		$C1 = $C1 + 0.0104 * sin( $dr * 2 * $F ) - 0.0051 * sin( $dr * ( $M + $Mpr));
		$C1 = $C1 - 0.0074 * sin( $dr * ( $M - $Mpr ) ) + 0.0004 * sin( $dr * ( 2 * $F + $M ));
		$C1 = $C1 - 0.0004 * sin( $dr * ( 2 * $F - $M ) ) - 0.0006 * sin( $dr * ( 2 * $F + $Mpr ));
		$C1 = $C1 + 0.0010 * sin( $dr * ( 2 * $F - $Mpr ) ) + 0.0005 * sin( $dr * ( 2 * $Mpr + $M ));
		if ( $T < -11 )
		{
						$deltat = 0.001 + 0.000839 * $T + 0.0002261 * $T2 - 0.00000845 * $T3 - 0.000000081 * $T * $T3 ;
		}
		else
		{
						$deltat = -0.000278 + 0.000265 * $T + 0.000262 * $T2;
		}
		
		$JdNew = $Jd1 + $C1 - $deltat;
		//echo "JdNew = $JdNew\n";
		return $this::INT( $JdNew + 0.5 + $timeZone / 24 );
	}
 
	public function getSunLongitude( $jdn, $timeZone )
	{
		$T = ( $jdn - 2451545.5 - $timeZone / 24 ) / 36525; // Time in Julian centuries from 2000-01-01 12:00:00 GMT
		$T2 = $T * $T;
		$dr = M_PI / 180; // degree to radian
		$M = 357.52910 + 35999.05030 * $T - 0.0001559 * $T2 - 0.00000048 * $T * $T2; // mean anomaly, degree
		$L0 = 280.46645 + 36000.76983 * $T + 0.0003032 * $T2; // mean longitude, degree
		$DL = ( 1.914600 - 0.004817 * $T - 0.000014 * $T2 ) * sin( $dr * $M );
		$DL = $DL + ( 0.019993 - 0.000101 * $T ) * sin( $dr * 2 * $M ) + 0.000290 * sin( $dr * 3 * $M );
		$L = $L0 + $DL; // true longitude, degree
		//echo "\ndr = $dr, M = $M, T = $T, DL = $DL, L = $L, L0 = $L0\n";
		// obtain apparent longitude by correcting for nutation and aberration
		$omega = 125.04 - 1934.136 * $T;
		$L = $L - 0.00569 - 0.00478 * sin( $omega * $dr );
		$L = $L * $dr;
		$L = $L - M_PI * 2 * ( $this::INT( $L / ( M_PI * 2 ) ) ); // Normalize to (0, 2*PI)
		return $this::INT( $L / M_PI * 6 );
	}
 
	public function getLunarMonth11( $yy, $timeZone )
	{
		$off = $this->jdFromDate( 31, 12, $yy ) - 2415021;
		$k = $this::INT( $off / 29.530588853 );
		$nm = $this::getNewMoonDay( $k, $timeZone );
		$sunLong = $this::getSunLongitude( $nm, $timeZone ); // sun longitude at local midnight
		if ( $sunLong >= 9 )
		{
						$nm = $this::getNewMoonDay( $k - 1, $timeZone );
		}
		return $nm;
	}
 
	public function getLeapMonthOffset( $a11, $timeZone )
	{
		$k = $this::INT( ( $a11 - 2415021.076998695 ) / 29.530588853 + 0.5 );
		$last = 0;
		$i = 1; // We start with the month following lunar month 11
		$arc = $this::getSunLongitude( $this::getNewMoonDay( $k + $i, $timeZone ), $timeZone );
		do
		{
			$last = $arc;
			$i = $i + 1;
			$arc = $this::getSunLongitude( $this::getNewMoonDay( $k + $i, $timeZone ), $timeZone );
		} 
		while ( $arc != $last && $i < 14 );
		return $i - 1 ;
	}
 
	/* Comvert solar date dd/mm/yyyy to the corresponding lunar date */
	public function convertSolar2Lunar( $dd, $mm, $yy, $timeZone )
	{
		$dayNumber = $this::jdFromDate( $dd, $mm, $yy );
		$k = $this::INT( ( $dayNumber - 2415021.076998695 ) / 29.530588853 );
		$monthStart = $this::getNewMoonDay( $k + 1, $timeZone );
		if ($monthStart > $dayNumber)
		{
			$monthStart = $this::getNewMoonDay( $k, $timeZone );
		}
		$a11 = $this::getLunarMonth11( $yy, $timeZone ) ;
		$b11 = $a11 ;
		if ( $a11 >= $monthStart )
		{
			$lunarYear = $yy;
			$a11 = $this::getLunarMonth11( $yy - 1, $timeZone );
		}
		else
		{
			$lunarYear = $yy + 1;
			$b11 = $this::getLunarMonth11( $yy + 1, $timeZone );
		}
		$lunarDay = $dayNumber - $monthStart + 1 ;
		$diff = $this::INT( ( $monthStart - $a11 ) / 29 ) ;
		$lunarLeap = 0 ;
		$lunarMonth = $diff + 11 ;
		if ( $b11 - $a11 > 365 )
		{
			$leapMonthDiff = $this::getLeapMonthOffset( $a11, $timeZone ) ;
			if ( $diff >= $leapMonthDiff )
			{
							$lunarMonth = $diff + 10 ;
							if ( $diff == $leapMonthDiff )
							{
											$lunarLeap = 1 ;
							}
			}
		}
		if ( $lunarMonth > 12 )
		{
			$lunarMonth = $lunarMonth - 12 ;
		}
		if ( $lunarMonth >= 11 && $diff < 4 )
		{
			$lunarYear -= 1 ;
		}
		return array(
						$lunarDay,
						$lunarMonth,
						$lunarYear,
						$lunarLeap ) ;
	}
 
	/* Convert a lunar date to the corresponding solar date */
	public function convertLunar2Solar( $lunarDay, $lunarMonth, $lunarYear, $lunarLeap,
					$timeZone )
	{
		if ( $lunarMonth < 11 )
		{
						$a11 = $this::getLunarMonth11( $lunarYear - 1, $timeZone ) ;
						$b11 = $this::getLunarMonth11( $lunarYear, $timeZone ) ;
		}
		else
		{
						$a11 = $this::getLunarMonth11( $lunarYear, $timeZone ) ;
						$b11 = $this::getLunarMonth11( $lunarYear + 1, $timeZone ) ;
		}
		$k = $this::INT( 0.5 + ( $a11 - 2415021.076998695 ) / 29.530588853 ) ;
		$off = $lunarMonth - 11 ;
		if ( $off < 0 )
		{
						$off += 12 ;
		}
		if ( $b11 - $a11 > 365 )
		{
						$leapOff = $this::getLeapMonthOffset( $a11, $timeZone ) ;
						$leapMonth = $leapOff - 2 ;
						if ( $leapMonth < 0 )
						{
										$leapMonth += 12 ;
						}
						if ( $lunarLeap != 0 && $lunarMonth != $leapMonth )
						{
										return array(
														0,
														0,
														0 ) ;
						}
						else
										if ( $lunarLeap != 0 || $off >= $leapOff )
										{
														$off += 1 ;
										}
		}
		$monthStart = $this::getNewMoonDay( $k + $off, $timeZone ) ;
		return $this::jdToDate( $monthStart + $lunarDay - 1 ) ;
	}
}
 
$dateconverter=new Duong2amlich();

$al_ngay=1;
$al_thang=1;
$al_nam=date('Y')+1;
$lathangnhuan=0; //nếu là tháng nhuận thì set giá trị là 1
$timezone='7.0';

$ngayduonglich=$dateconverter->convertLunar2Solar($al_ngay, $al_thang, $al_nam, $lathangnhuan,$timezone);
$ngay_d = $ngayduonglich[0];
$thang_d = $ngayduonglich[1];
$nam_d = $ngayduonglich[2];
if($ngay_d < 10){
	$ngay_d = '0'.$ngay_d;
}
if($thang_d < 10){
	$thang_d = '0'.$thang_d;
}
$tet = $nam_d.'-'.$thang_d.'-'.$ngay_d.'T00:00:00';
?>
<!DOCTYPE html>
  <head>
    <meta http-equiv="content-type" content="text/html; charset=UTF8">
    <title>Happy New Year <?=date('Y')+1?></title>    
	<meta content='width=device-width,initial-scale=1.0,minimum-scale=1.0,maximum-scale=1.0' name='viewport'/>
	 <link href="images/icon.ico" rel="shortcut icon" type="image/vnd.microsoft.icon" meta name="theme-color" content="#880043">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<link href='https://fonts.googleapis.com/css?family=Roboto+Condensed:400,300italic,300,400italic,700,700italic&amp;subset=latin,vietnamese' rel='stylesheet' type='text/css'/>
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" />
	<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap"
            rel="stylesheet">
	<link href='https://fonts.googleapis.com/css?family=Raleway:400,500,700|Open+Sans:800' rel='stylesheet' type='text/css'/>
	<link href='https://fonts.googleapis.com/css?family=Quicksand' rel='stylesheet' type='text/css'/>
	<link href='https://netdna.bootstrapcdn.com/font-awesome/4.5.0/css/font-awesome.css' rel='stylesheet'/>
	<link href="css/fireworks.css" rel="stylesheet">
	<link rel="stylesheet" type="text/css" href="css/component.css" />
	<link href="css/soon.min.css" rel="stylesheet"/>		
	<link href="lib/animate.css" rel="stylesheet"/>		
	<style>
		body {
			margin: 0;
			padding: 0;
			text-align: center;
			width: 100%;
		}
		a {
			color: #ccc;
			text-decoration: none;
		}
		#container {
			margin: 0;
			padding: 0;
			height: 100%;
			position:relative;
		}
		.hi-icon-wrap{
			/*background: url("images/background.png") bottom center repeat-x;*/
		}
		#canvastext{
			/*background: transparent url(images/bg.png) top center no-repeat;*/
			background-color: transparent;
			background-image: url(https://lh3.googleusercontent.com/--vCv4c2ydA4/VouNG4inXsI/AAAAAAAACds/sMkZeNpv62g/s0-Ic42/bg.png), url(https://lh3.googleusercontent.com/-ociyNHFTLno/VouOOmMG97I/AAAAAAAACeE/q3-PK_BG9Z4/s0-Ic42/background.png);
			background-position: top center, bottom center;
			background-repeat: no-repeat; repeat-x;
			background-size: 100% auto, 100% auto;
			z-index: 5;
			position: absolute;
			top: 0;
			left: 0;
		}
				
		canvas:nth-child(2){
			z-index: 1;
			position: relative;
			background: transparent;
		}
		canvas{
			width: 100%;
			height: auto;
		}
		
		#home-button{
			background: transparent url(https://lh3.googleusercontent.com/-eLiHloXbZyc/VouNHAFHZII/AAAAAAAACds/98WzBxhr3Uw/s236-Ic42/4.png) center center no-repeat;
			background-size: 100% auto;
			z-index: 6;
			position: relative;
			width: 236px;
			height: 136px;
			cursor: pointer;
			display: inline-block;
		}
		
		#home-button a.homepage-btn{
			text-align: center;
			position: absolute;
			width: 100%;
			bottom: 0;
			left: 0;
			font-family: Impact, Arial, sans-serif;
			font-size: 24px;
			color: #F3D60C;
			line-height: 25px;
		}
		
		#fireWork{
			position: absolute;
			width:100%;
			top: 0;
			left:0;			
		}
		
		.content-logo-wrap{
			position: absolute;
			width: 100%;
			z-index: 2;
			bottom: 50px;
			left:0;
		}
		
		.content-logo {
			font: 800 14.5em/1  'Open Sans', Impact; 
			position: relative;			
			width: 100%;	
			text-align: center;
			opacity: 0.15;
		}

		svg:not(:root) {
			overflow: hidden;
		}
		
		svg.sgmlogo {
			width: 80%;
			margin: 0 auto 50px;
			display: block;
			text-transform: uppercase; 
		}

		.text {
			fill: none;
			stroke-width: 6;
			stroke-linejoin: round;
			stroke-dasharray: 70 330;
			stroke-dashoffset: 0;
			-webkit-animation: stroke 6s infinite linear;
			animation: stroke 6s infinite linear; 
		}
		.text:nth-child(5n + 1) {
			stroke: #F2385A;
			-webkit-animation-delay: -1.2s;
			animation-delay: -1.2s; 
		}
		.text:nth-child(5n + 2) {
			stroke: #F5A503;
			-webkit-animation-delay: -2.4s;
			animation-delay: -2.4s; 
		}
		.text:nth-child(5n + 3) {
			stroke: #E9F1DF;
			-webkit-animation-delay: -3.6s;
			animation-delay: -3.6s; 
		}
		.text:nth-child(5n + 4) {
			stroke: #56D9CD;
			-webkit-animation-delay: -4.8s;
			animation-delay: -4.8s; 
		}
		.text:nth-child(5n + 5) {
			stroke: #3aa1bf;
			-webkit-animation-delay: -6s;
			animation-delay: -6s; 
		}

		@-webkit-keyframes stroke {
			100% {
			stroke-dashoffset: -400; } 
		}
		@keyframes stroke {
			100% {
			stroke-dashoffset: -400; } 
		}
		
		
		#count-down-wrapper {
			position: absolute;
			width:100%;
			top: 50%;
			left:0;
			text-align:center;
			z-index:6;
			/*transform: translateY(-45%);*/
		}		
		
		#soon-glow {
			font-family: 'Quicksand', sans-serif;
			color:#fff;
			background:transparent;
			/*background-color:#f13446;
			background-image:linear-gradient(30deg, #F13B6F,#FC9E2C);*/
			text-transform:lowercase;
		}
		#soon-glow .soon-label {
			color:#fff;
			text-shadow:0 0 .25rem rgba(255,255,255,.75);
		}
		#soon-glow .soon-ring-progress {
			color:#fff;
			background-color:rgba(255,255,255,.15);
		}
		#soon-glow>.soon-group {
			margin-bottom:-.5em;
		}
		.soon[data-layout*=group] {
			padding: 0.5em 0;		
		}
		
		.SLToday {
			font-family: "Roboto Condensed";
			color: #FFF;
			font-size: 25px;
		}
		
		#footer-controls{
			position: absolute;
			bottom:0;
			left:0;
			width: 100%;
			z-index:20;
			background: transparent;
		}
		
		#footer-controls a{
			float:right;
			display: block;
			padding:3px 5px;
			color: #FFF;
			font-size: 14px;
			margin: 0 5px;
		}
		
		#footer-controls a:hover{
			color: #FFFF00;
		}
		
		#footer-controls .music-control{
			margin-right: 30px;
			font-family: "Roboto Condensed", sans-serif;
		}
		
		#footer-controls .music-control i{
			margin-left: 5px;
		}
		
		#footer-controls .fb{
			color: #3E588E;
		}
		
		#footer-controls .yt{
			color: #E62117;
		}
		
		#footer-controls .gp{
			color: #D94E43;
		}
		
		#footer-controls .pin{
			color: #BD2126;
		}
		
		.jp-jplayer, .jp-jplayer audio {
			width: 0;
			height: 0;
		}
		
		.mkCD{
			position: absolute;
			z-index: 15;
			top: 0;
			left: 0;
			display: block;		
			width:140px; 
			height:182px;
			cursor:pointer;
		}
		.textCD{
			width: 0px;
			height: 0px;
			background: #FFF url(https://i.imgur.com/5xml3Ul.jpg) top center no-repeat;
			background-size: 100% 100%;
			display: block;
			position: absolute;	
			z-index: 14;
		}
		.textCD .texts1{
			padding: 30px;
			text-align: center;
			font-family: "Roboto Condensed", Arial, sans-serif;
			font-size: 18px;
			font-weight: bold;
		}
		.textCD .texts1:first-child{
			padding-top: 40px;
			padding-bottom: 0;
			text-transform: uppercase;
		}
		
		@media only screen and (max-width: 850px) {
			#canvastext2{
				width: 200px;
			}
			#home-button{
				width: 200px;
			}
			.hi-icon {				
				margin: 10px 15px;				
			}
		}
		@media only screen and (max-width: 768px) {
			.hi-icon {				
				margin: 10px 3px;
				width: 50px;
				height: 50px;				
			}
			.hi-icon:before{
				font-size:38px;
			}
			#canvastext2{
				width: 120px;
			}
			#home-button{
				width: 120px;
			}
		}
	</style>
	<style>
	    
	</style>
  </head>
  <body>
      
    
	<div class="content-logo-wrap">
		<div class="content-logo">
			<svg class="sgmlogo" viewBox="0 0 1100 300">
				<!-- Symbol -->
				<symbol id="s-text">
					<text id="nam-gi" text-anchor="middle" x="50%" y="50%" dy=".35em">
					</text>
				</symbol>

				<!-- Duplicate symbols -->
				<use xlink:href="#s-text" class="text"></use>
				<use xlink:href="#s-text" class="text"></use>
				<use xlink:href="#s-text" class="text"></use>
				<use xlink:href="#s-text" class="text"></use>
				<use xlink:href="#s-text" class="text"></use>
			</svg>
		</div>
	</div>
	<div id="container">	
	<!-- Hiding library elements in the DOM is fun -->
    <aside id="library">
      <img src="https://lh3.googleusercontent.com/-SJqVqUzXLWI/VouNHyRXgrI/AAAAAAAACds/zgM_5GWaBIA/s0-Ic42/nightsky.png" id="nightsky" />
      <img src="https://lh3.googleusercontent.com/-c7qitldyp14/VouNH9DY9DI/AAAAAAAACds/BPrXaUP-d2w/s0-Ic42/big-glow.png" id="big-glow" />
      <img src="https://lh3.googleusercontent.com/-2b4-lPI_YMY/VouNIJXvfeI/AAAAAAAACds/IL_ETBE7kQo/s0-Ic42/small-glow.png" id="small-glow" />
	  <img src="https://lh3.googleusercontent.com/-vU3Oac1_HWU/VouPN8OOc1I/AAAAAAAACec/sHtg7wSdTwU/s0-Ic42/moon.png" alt="" id="moon" style="visibility: hidden;">
    </aside>
	<canvas id="canvastext"></canvas>	
	
	<div class="hi-icon-wrap hi-icon-effect-8">
		<a href="#" class="hi-icon hi-icon-photo">Photo</a>
		<a href="#" class="hi-icon hi-icon-video">Video</a>		
		<div id="home-button"> 								
		<a href="." target="_self" class="homepage-btn">Go To Homepage </a> 
		
		</div>
		<a href="#" class="hi-icon hi-icon-music">Music</a>
		<a href="#" class="hi-icon hi-icon-livetv">TV</a>		
	</div>
	
	<div id="count-down-wrapper">
		<div class="SLToday"></div>
		<div class="soon" id="soon-glow"
			 data-layout="group overlap"
			 data-face="slot doctor glow"
			 data-padding="false"
			 data-scale-max="l"
			 data-visual="ring color-light width-thin glow-progress length-70 gap-0 offset-65"
			 data-labels-days="Ngày,Ngày"
			 data-labels-hours="Giờ,Giờ"
			 data-labels-minutes="Phút,Phút"
			 data-labels-seconds="Giây,Giây"
			 data-due="<?=$tet?>">
		</div>
	</div>
	</div>
	<div id="footer-controls">
		<a href="javascript:void(0);" class="music-control" data-play-pause="play">Tắt nhạc <i class="fa fa-pause-circle"></i></a>
		<a href="https://github.com/tiendat-NTD" target="_blank"><i class="fab fa-github"></i></a>
		<a href="https://www.instagram.com/tiendat.69/" target="_blank"><i class="fab fa-instagram"></i></a>
		<a href="mailto:tiendat.8498@gmail.com" class="gp" target="_blank"><i class="fab fa-google" style="color: red;"></i></a>
		<a href="https://www.tiktok.com/@tiendat.69" target="_blank"><i class="fab fa-tiktok"></i></a>
		<a href="https://m.me/Mr.Zombie69" target="_blank"><i style="color: #0089ff;" class="fab fa-facebook-messenger"></i></a>
		<a href="https://www.facebook.com/Mr.Zombie69/" target="_blank" class="fb"><i class="fab fa-facebook"></i></a>
		
		<div style="clear:both;"/>
	</div>	
	<div id="bg-music" class="jp-jplayer"></div>
  </body>
  <script src='https://ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js' type='text/javascript'></script>
  <script src="lib/soundjs-0.6.2.min.js"></script>
  <script src="lib/flashaudioplugin-0.6.2.min.js"></script>
  <script src="lib/jplayer/jquery.jplayer.min.js"></script>  
  <script>
	createjs.FlashAudioPlugin.swfPath = "lib/flashaudio/FlashAudioPlugin.swf";
	createjs.Sound.registerPlugins([createjs.WebAudioPlugin, createjs.HTMLAudioPlugin, createjs.FlashAudioPlugin]);
  </script>
  <script src="js/requestanimframe.js"></script>
  <script src="js/fireworks.min.js"></script>
  <script src="lib/moment.js"></script>
  <script src="lib/moment-timezone-with-data.js"></script>
  <script src="lib/amlich-ntt.js"></script>  
  <script src="lib/soon.min.js" data-auto="false"></script>
  <script src="lib/jquery.lettering.js"></script>
  <script src="lib/jquery.textillate.js"></script>
  
  <script>
var canvas=document.getElementById("canvastext"),ctx=canvas.getContext("2d"),W=window.innerWidth,H=window.innerHeight,text="<?=date('Y')+1?>",text2="Chúc Mừng Năm Mới",skipCount=4,gravity=.2,touched=!1,mouse={},minDist=20,bounceFactor=.6;canvas.height=H;canvas.width=W;function trackPos(a){mouse.x=a.pageX;mouse.y=a.pageY}
var Particle1=function(){this.r=6*Math.random();this.y=this.x=-100;this.vy=-5+parseInt(10*Math.random());this.vx=-5+parseInt(10*Math.random());this.isFree=!1;this.a=Math.random();this.draw=function(){ctx.beginPath();ctx.fillStyle="rgba(255, 223, 0, "+this.a+")";ctx.arc(this.x,this.y,this.r,0,2*Math.PI,!1);ctx.fill();ctx.closePath()};this.setPos=function(a,d){this.x=a;this.y=d}},particles1=[];
(function(){ctx.fillStyle="black";ctx.font="100px Arial, sans-serif";ctx.textAlign="center";ctx.fillText(text,W/2,H/3);ctx.fillStyle="#29a1f1";ctx.font="70px Arial, sans-serif";ctx.fillText(text2,W/2,H/3+100)})();(function(){})();(function(){for(var a=ctx.getImageData(0,0,W,W),d=a.data,b=0;b<a.height;b+=skipCount)for(var c=0;c<a.width;c+=skipCount)255==d[c*a.width*4+4*b-1]&&(particles1.push(new Particle1),particles1[particles1.length-1].setPos(b,c))})();function clear(){ctx.clearRect(0,0,W,H)}
function update1(){clear();for(i=0;i<particles1.length;i++){var a=particles1[i];a.r+=.15;a.a-=.015;0>a.a&&(a.r=6*Math.random(),a.a=Math.random());mouse.x>a.x-a.r&&mouse.x<a.x+a.r&&mouse.y>a.y-a.r&&mouse.y<a.y+a.r&&(touched=!0);1==touched&&(Math.sqrt((a.x-mouse.x)*(a.x-mouse.x)+(a.y-mouse.y)*(a.y-mouse.y))<=minDist&&(a.isFree=!0),1==a.isFree&&(a.y+=a.vy,a.x+=a.vx,a.vy+=gravity,a.y+a.r>H&&(a.vy*=-bounceFactor,a.y=H-a.r,a.vx=0<a.vx?a.vx-.1:a.vx+.1),a.x+a.r>W&&(a.vx*=-bounceFactor,a.x=W-a.r),0>a.x-a.r&&
(a.vx*=-bounceFactor,a.x=a.r)));ctx.globalCompositeOperation="lighter";a.draw()}}(function animloop(){requestAnimFrame(animloop);update1()})();
  	
$(document).ready(function(){

	var listBgMusic = [		
		{
			title: "Chuyện cũ bỏ qua",
			artist:"Bích phương Phương",
			mp3:"music/Chuyen Cu Bo Qua Remix - - - Bich Phuong.mp3"
		},{
			title: "Tết đong đầy",
			artist:"Kay Trần",
			mp3:"music/Tet Dong Day - Kay Tran_ Nguyen Khoa.mp3"
		},{
			title: "Tết là tết",
			artist:"Thuý Khanh, Tiến Lam",
			mp3:"music/Tet La Tet - Huu Tho.mp3"
		},{
			title: "Tết nguyên đán",
			artist:"Thanh Thảo",
			mp3:"music/Tet Nguyen Dan - Thanh Thao.mp3"
        },{
			title: "Chúc tết",
			artist:"Khởi My",
			mp3:"music/Chuc Tet - Khoi My.mp3"
        },{
			title: "Tết Miền Tây",
			artist:"Khưu Huy Vũ",
			mp3:"music/Tet Mien Tay - Duong Hong Loan.mp3"
        },{
			title: "Tết phát tài",
			artist:"Cẩm Ly",
			mp3:"music/Tet Phat Tai - Cam Ly.mp3"
        }
		
	];
	
	var ranBg = Math.floor(Math.random() * listBgMusic.length);
	
	var MBG = $("#bg-music").jPlayer({
        ready: function () {
          $(this).jPlayer("setMedia", listBgMusic[ranBg]).jPlayer("play");
        },
		ended: function() {
			var ranBgNew = Math.floor(Math.random() * listBgMusic.length);
			while(ranBgNew == ranBg){
				ranBgNew = Math.floor(Math.random() * listBgMusic.length);
			}	
			$(this).jPlayer("setMedia", listBgMusic[ranBgNew]).jPlayer("play");
		},
		error: function(){
			var ranBgNew = Math.floor(Math.random() * listBgMusic.length);
			while(ranBgNew == ranBg){
				ranBgNew = Math.floor(Math.random() * listBgMusic.length);
			}	
			$(this).jPlayer("setMedia", listBgMusic[ranBgNew]).jPlayer("play");
		},
        cssSelectorAncestor: "",		
        swfPath: "lib/jplayer/jquery.jplayer.swf",
        supplied: "m4a,oga,mp3",
		preload: "auto",
		volume: 1
    });

	$("#footer-controls a.music-control").on("click", function(){
		var curState = $(this).data("play-pause");
		if(curState == "play"){
			MBG.jPlayer("pause");
			$(this).data("play-pause", "pause").html('Bật nhạc <i class="fa fa-play-circle"></i>');
		}else{
			MBG.jPlayer("play");
			$(this).data("play-pause", "play").html('Tắt nhạc <i class="fa fa-pause-circle"></i>');
		}
	});	
		     
	$('#count-down-wrapper .soon').attr('data-now',moment().format());     
	var soon = document.querySelectorAll('#count-down-wrapper .soon');
	Soon.create(soon[0]);
		
	var vnCurrentTime = moment.tz("Asia/Saigon");
	var dd = vnCurrentTime.date();
	var mm = vnCurrentTime.month()+1;
	var yy = vnCurrentTime.year();
	var dd2 = Number('01');
	var mm2 = Number('01');
	var yy2 = Number(<?=date('Y')+1?>);
	var ld = getLunarDate(dd, mm, yy);
	var ld2 = getSolarDate(dd2, mm2, yy2);
	var ngay = ld2[0];
	var thang = ld2[1];
	var nam = ld2[2];
	if(ngay < 10){
		ngay = '0'+ngay;
	}
	if(thang < 10){
		thang = '0'+thang;
	}
	var giao_thua = nam+'-'+thang+'-'+ngay+'T00:00:00';
	console.log(giao_thua);
	var text_nam = ld.year+1;
	// $('#soon-glow').data('due', String(giao_thua));
	// $('#soon-glow').attr("data-due",giao_thua);
	// $('#count-down-wrapper .soon').attr('data-due', '2022-02-01T00:00:00');    
	
	$(".SLToday").html("Hôm nay: " + vnCurrentTime.format("DD-MM-YYYY") + " <i class='fa fa-calendar'></i> " + ld.day + "-" + ld.month + "-" + ld.year + ", " + getYearCanChi(ld.year));
	$("#nam-gi").html(getYearCanChi(text_nam));
		
	
	var monkeyCD = ["https://i.imgur.com/92F1YOo.png", "https://i.imgur.com/92F1YOo.png", "https://i.imgur.com/92F1YOo.png", "https://i.imgur.com/92F1YOo.png"];
	var textCD = [
		"TIMT chúc bạn cùng gia đình tràn đầy sức khỏe, thành công và hạnh phúc.",
		"Năm mới Tết đến. Rước hên vào nhà. Quà cáp bao la. Mọi nhà no đủ. Vàng bạc đầy hũ. Gia chủ phát tài. Già trẻ gái trai. Sum vầy hạnh phúc. Cầu tài chúc phúc. Lộc đến quanh năm. An khang thịnh vượng!",
		"Năm mới: Ngàn lần như ý, Vạn lần như mơ, Triệu sự bất ngờ, Tỷ lần hạnh phúc.",
		"Chúc bạn: 12 tháng phú quý, 365 ngày phát tài, 8760 giờ sung túc, 525600 phút thành công 31536000 giây vạn sự như ý.",
		"Sang năm mới chúc mọi người có một bầu trời sức khoẻ, một biển cả tình thương, một đại dương tình cảm, một điệp khúc tình yêu, một người yêu chung thủy, một tình bạn mênh mông, một gia đình thịnh vượng."
	];
	var monkeyX = $(window).width()/2 - $(".soon-group").outerWidth()/2 - 110 - 10;
	var idcd = Math.floor(Math.random() * monkeyCD.length);
	
	var monkey = '<div id="mcd" style="display:block;width:140px; height:182px;position: absolute;z-index:30; top:0; left: ' + monkeyX + 'px"><div class="mkCD" style="background: transparent url('+ monkeyCD[idcd] +') center center no-repeat;background-size:100% 100%;"></div><div class="textCD"><div class="texts1">Chúc Mừng Năm Mới <?=date("Y")+1?></div><div class="texts1">'+ textCD[idcd] +'</div></div></div>';
	
	//$("#count-down-wrapper").append(monkey);
	
	$(window).resize(function(){
		monkeyX = $(window).width()/2 - $(".soon-group").outerWidth()/2 - 110 - 10;
		$("#mcd").css("left", monkeyX+"px");
	});
	
	$('.texts1').textillate({		
		autoStart: false,
		in: { effect: 'flipInY' },
		out: { effect: 'hinge' }		
	});
	
	$("#mcd").on('mouseenter', function(){
		$(this).animate({width: "+=100", height: "+=130", top: "-=65"});
		$(".mkCD").animate({width: "+=100", height: "+=130"});
		$(".textCD").animate({width: "+=457", height: "+=210", left: "+=200"});
		
		$('.texts1').textillate('start');
		$('.texts1').textillate('in');
	})
	.on('mouseleave', function(){
		$(this).animate({width: "-=100", height: "-=130", top: 0});
		$(".mkCD").animate({width: "-=100", height: "-=130"});
		$(".textCD").animate({width: "-=457", height: "-=210", left: 0});
				
		$('.texts1').textillate('out');		
	})	
		
});

</script>
</html>
