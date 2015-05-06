<table border="0">
<tbody>
<?php

foreach ($data as $row)
{
	echo ('<tr>');
	
	foreach ($row as $column)
	{
		echo ('<td>' . $column . '</td>');
	}
	
	echo ('</tr>');
}

?>
</tbody>
</table>