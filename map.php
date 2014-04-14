<?php include("config.class.php"); ?>
<table><tr>
<td>
    <div style="width:1040px;height:620px" id="map"></div>
</td><td valign="top">
Enter some IPs to plot
    <form action="javascript:plot_or_stop();">
    <textarea name="ip" id="ip" cols="29" rows="28">
128.95.155.198
200.89.76.16
194.158.78.228
193.111.11.3
131.217.10.176
</textarea>
<br />

    <input type="submit"  name="submit" id="submit" value="Plot" style="width:140px;" onClick="" />
    <br><br>
    <div>To plot: <b><span id="toplot">??</span></b>
    <div>Plotted: <b><span id="plotted">0</span></b>
    <div>Unknown: <b><span id="unknown">0</span></b>

</form></td>
</tr>
</table>
<script type="text/javascript" 
	src="http://maps.googleapis.com/maps/api/js?v=3&sensor=false&key=<?php print $key; ?>"></script>
<script type="text/javascript">

    var mapOptions = {
	zoom: 2,
	center: (new google.maps.LatLng(0, 0)),
	mapTypeId: google.maps.MapTypeId.ROADMAP
    }
    var map = new google.maps.Map(document.getElementById("map"), mapOptions);

    //You can add a default static marker to your map and cet the centre like so:
    /*
    //Precise location of NEBC HQ (thanks to streetmap.co.uk)
    var testmarker = (new google.maps.LatLng(51.602798,-1.110811));
    var testtitle = "NEBC HQ";
    
    if(testmarker){
    var marker = new google.maps.Marker({
	map: map,
	position: testmarker,
	flat: true,
	icon: "http://www.google.com/intl/en_us/mapfiles/ms/micons/green-dot.png",
	title: testtitle,
	zIndex: ( google.maps.Marker.MAX_ZINDEX + 1 )
        })
    };
    */


</script>
