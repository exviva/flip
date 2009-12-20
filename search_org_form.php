<?php
	require_once('lib/flip.php');
	session_start();
	check_valid_user();

	display_html_header();
	display_document_header();
	display_menu();
?>
<table width="90%">
<tr><td align="center" class="naglowek">Znajd¼ organizacjê<hr></td></tr>
<tr><td><form method="POST" action="search_org.php">
<input type="text" name="search" maxlength="100" size="60"></td></tr>
<tr><td align="left"><input type="submit" value="Szukaj"></td></tr>
</table>
<script type="text/javascript">
<!--
	document.forms[0]["search"].focus();
// -->
</script>
<?php
	display_document_footer();
?>
