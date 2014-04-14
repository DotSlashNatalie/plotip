<head>
  <title>IP Geolocation plotter</title>
  <!-- Load Prototype farmework -->
  <script src="lib.js" ></script>
  <script type="text/javascript">

  //This file contains the main Javacript for plotting...

  // Plotting speed - delay in ms
  var sleeptime = 100;

  //The counters:
  var toplot;
  var plotted;
  var unknown;

  //Handle for clearTimeout()
  var submit_button;
  var t_o;

  function plot_or_stop(){

      //If plot already running, stop
      if(t_o)
      {
	  shtop(); return;
      }

    // Grab IP list and split on any sensible delimiter
    var list = $('ip').value.split(/[\s,:;]+/);
    if(list[0].empty()) list.shift();
    if(list[list.length-1].empty()) list.pop();

    toplot = document.getElementById("toplot").firstChild;
    toplot.nodeValue = list.length;

    plotted = document.getElementById("plotted").firstChild;
    unknown = document.getElementById("unknown").firstChild;

    submit_button = document.getElementById("submit");
    if(submit_button)
    {
	submit_button.old_value = submit_button.value;
	submit_button.value = "Stop";
    }

    plotnsleep( list, 0, sleeptime );

  }

  function plotnsleep( list, offset, timeoutms )
  {
      //Plot with some timeouts
      t_o = null;

      if(offset >= list.length)
      {
	  //We're all done
	  shtop();
	  return;
      }

      plotip(list[offset]);

      //DEBUG:
      //return;

      t_o = setTimeout( function() {
	  plotnsleep( list, offset+1, timeoutms )
      }, timeoutms );
  }

  function shtop()
  {
      if(t_o)
      {
	  clearTimeout(t_o);
	  t_o = null;
      }

      if(submit_button)
      {
	  submit_button.value = submit_button.old_value;
      }
  }

  //This function is called as an async callback from plotip below
  function putonmap(txt,ip){
    var latlong = txt.split(":");

    var point = new google.maps.LatLng(latlong[0],latlong[1]);

    //This may help debugging:
     //alert("Plotting LatLong:" + txt + " Point:" + point);

    if(isNaN(point.lat()))
    {
	unknown.nodeValue++;
    }
    else
    {
	var marker = new google.maps.Marker({ 
	    map: map,
	    position: point, 
	    flat:true});
	marker.setTitle(ip + " in " + latlong[2] + " (" + latlong[0] + "," + latlong[1] + ")");

	plotted.nodeValue++;
    }
  }

  // This is called per IP to add.  It fetches the co-ordinates via
  // request.php and bungs them on the map as a marker, via an
  // async call to putonmap()
  function plotip(ip){
    new Ajax.Request('request.php?ip='+ip,
    {
	method:'get',
	onSuccess: function(transport){
	    var response = transport.responseText || "no response text";
	    putonmap(response,ip);
	},
	onFailure: function(){ alert('Something went wrong.  Try calling request.php?ip=... manually.') }
  });
  }
  </script>
</head>

