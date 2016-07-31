<script type="text/javascript">
<!--
var Sample = {
wait : 1200, //Wait Time(Second)
url : "http://192.1.10.136/sys/cvs/logout.php" //Location
};
Sample.record = function() {
this.timeout = +new Date() + this.wait * 1000;
};
Sample.check = function() {
if (this.timeout == undefined) this.record();
if (this.timeout - new Date() < 0) location.href = this.url;
}
//@cc_on
document./*@if(1)attachEvent('on' + @else @*/addEventListener(/*@end @*/
'mousemove', function(){ Sample.record() }, false);
setInterval(function(){ Sample.check() }, 500);
//-->
</script>
