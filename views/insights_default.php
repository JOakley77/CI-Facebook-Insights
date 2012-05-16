<h1>Facebook Insights Datepicker</h1>
	
<div class="form">
	<form action="<?= base_url() ?>insights_test/results" method="get">
		<p>
			<label for="datepicker">From Date</label>
			<span class="field"><input type="text" name="date_from" id="datepicker_from" /></span>
		</p>

		<p>
			<label for="datepicker">Until Date</label>
			<span class="field"><input type="text" name="date_until" id="datepicker_until" /></span>
		</p>

		<input type="hidden" name="access_token" value="<?= $token ?>" />

		<div>
			<input type="submit" value="Get Index" class="submit" />
		</div>
	</form>
</div>