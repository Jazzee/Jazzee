<?php

/**
 * applicants_grid index view
 * @author Jon Johnson <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license.txt
 * @package jazzee
 * @subpackage admin
 */
?>
<noscript>This Page Requires javascript.  Please consult your department IT support for help enabling Javascript in your browser.</noscript>
<div id='ajaxstatus'></div>

<script id="DataHeaderTemplate" type="text/x-jquery-tmpl">
  <!-- one colgroup per column -->
 {{for names}}
   <colgroup></colgroup>
 {{/for}}
 <thead>

  <tr>
 {{for names}}
      <th class="page {{cssClass:#data}}">
	<span class="page-title">{{:#data}}</span>

      </th>
 {{/for}} 
  </tr>
 </thead>
 <tbody> <!-- id="ApplicantList"> -->

 </tbody>
</script>

<script id="RowTemplate" type="text/x-jquery-tmpl">

  <tr>
    <td><a href='{{:link}}'>View</a></td>
    <td>{{:lastName}}</td>
    <td>{{:firstName}}</td>
    <td>{{:email}}</td>
    <td>{{:updatedAt.date}}</td>
    <td>{{percent:percentComplete}}</td>
    <td>{{:lastLogin.date}}</td>
    <td>{{:createdAt.date}}</td>

    <td class="pages-placeholder"></td>
  </tr>
  
</script>

<script id="PageTableTemplate" type="text/x-jquery-tmpl">
      <td class="page {{cssClass:title}}">
        <div class="all-answers">

	   <table class="element-list answer answer-table tablesorter">
	{{for answers[0]}}

	   {{for elements}}

	<colgroup class="{{cssClass:title}}"></colgroup>
           {{/for}}
	{{/for}}
<thead>
        <tr class="sub-table-header-row">
	{{for answers[0]}}

	   {{for elements}}
<!-- title="{{:title}}" -->
	   <th class="element-header header-{{cssClass:title}}" >{{:title}}</th>
	   {{/for}}

	{{/for}}
	</tr>
</thead>
           </table>

	</div>
      </td>  
</script>


<script id="SubTableRowTemplate" type="text/x-jquery-tmpl">
  <tr class="sub-table-row"></tr>
</script>

<script id="ColumnTableTemplate" type="text/x-jquery-tmpl">
{{if ~checkReal(values[0].value)}}
<td answer="{{:answerNumber}}" class="answer-{{:answerNumber}} element element-{{cssClass:title}}" title="{{:title}}">{{:values[0].value}}</td>
{{/if}}
</script>


<span>Filter: <input type="text" name="filter" id="filter" value=""/></span>
<div id="grid">
  <table id="ApplicantTable" class="data-table tablesorter"></table>
</div>