<button onclick="topFunction()" id="goTop" class="dontdisplay" title="Go to top">Top</button>
<script type="text/javascript">
	window.onscroll = function() {scrollFunction()};
	function scrollFunction() {
	    if (document.body.scrollTop > 1000 || document.documentElement.scrollTop > 1000) {
	        document.getElementById("goTop").style.display = "block";
	    } else {
	        document.getElementById("goTop").style.display = "none";
	    }
	}
	function topFunction() {
	    document.body.scrollTop = 0;
	    document.documentElement.scrollTop = 0;
	}
</script>