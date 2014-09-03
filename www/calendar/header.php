<!DOCTYPE html>
<html lang="en">
<head>
  
  <title>Calendar</title>
  <meta charset="utf-8" />

  <link rel="stylesheet" href="calendar.css" type="text/css" />

</head>

<body>

  <header>
    <nav>
      <ul>  
        <li><a<?php if($activeTab == 'week') echo ' class="active"' ?> href="week.php">Week</a></li>
        <li><a<?php if($activeTab == 'month') echo ' class="active"' ?> href="month.php">Month</a></li>
        <li><a<?php if($activeTab == 'year') echo ' class="active"' ?> href="year.php">Year</a></li>
      </ul>
    </nav>
  </header>
