<pre>
<?php
require_once('config.php');
require_once('classes/wot.xml.php');

WotXML::init();
if (!WotXML::decodePackedFile(WOT_PATH . 'res\scripts\item_defs\vehicles\ussr\bt-7.xml', 'bt-7', 'bt-7.xml')) {
	echo WoTXML::$failed[0]['exception'];
}
?>
</pre>