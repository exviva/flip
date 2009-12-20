<?php
	require_once('lib/flip.php');
	session_start();
	check_valid_user();

	display_html_header();
	display_document_header();
	display_menu();
?>
<table width="90%">
<form method="post" action="feedback.php">
<tr><td align="center" class="naglowek">Prze¶lij uwagi<hr></td></tr>
<tr><td>
	<table align="center">
	<tr>
		<td>Temat:</td>
		<td><input size="60" type="text" name="subject"></td>
	</tr>
	<tr>
		<td valign="top">Tre¶æ:</td>
		<td><textarea name="body" cols="60" rows="15"></textarea></td>
	</tr>
	</table>
	</td>
</tr>
<tr><td align="center"><input type="submit" value="Prze¶lij"></td></tr>
</table>
<?php
	display_document_footer();	
?>
