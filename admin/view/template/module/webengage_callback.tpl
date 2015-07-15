<?php
	if(isset($wlc)){
		?>
		<html>
			<body>
				<form id="postbackToWp" name="postbackToWp" method="post" target="_top">
					<input type="hidden" name="weAction" value="wp-save"/>
					<input type="hidden" name="verification_message" value="<?php echo $vm; ?>"/>
					<input type="hidden" name="webengage_license_code" value="<?php echo $wlc; ?>"/>
					<input type="hidden" name="webengage_widget_status" value="<?php echo $wwa; ?>"/>					
				</form>
				<script type="text/javascript">
					document.getElementById("postbackToWp").setAttribute("action", parent.parent.window.location.href + "&noheader=true");
					document.getElementById("postbackToWp").submit();
				</script>
			</body>
		</html>
	<?php
	}		
	exit;
?>
