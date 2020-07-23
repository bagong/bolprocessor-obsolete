<?php
echo "<!DOCTYPE HTML>";
echo "<html lang=\"en\">";
echo "<head>";
echo "<meta content=\"text/html; charset=utf-8\" http-equiv=\"Content-Type\" />";
echo "<link rel=\"stylesheet\" href=\"bp.css\" />\n";
if(isset($filename)) echo "<title>".$filename."</title>\n";
else if(isset($this_title)) echo "<title>".$this_title."</title>\n";
echo "<script>\n";
echo "function show_trace(tracefile) {
window.open('tracefile','trace','width=800,height=300');
return;
}\n";
echo "</script>\n";
echo "<script>\n";
echo "function copyToClipboard(text) {
    var input = document.createElement('input');
    input.setAttribute('value', text);
    document.body.appendChild(input);
    input.select();
    var result = document.execCommand('copy');
    document.body.removeChild(input);
    alert('You copied: “'+text+'”');
    return result;
 }\n";
echo "</script>\n";
?>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js">
</script>
<script>
setTimeout(function() {
    $('#timespan').fadeOut('fast');
	}, 2000); // <-- time in milliseconds
</script>
<?php
echo "</head>";
echo "<body>\n";
?>
