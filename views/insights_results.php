<h1>Results</h1>
<div class="range"><strong>Date Range:</strong> <span><?= $insights->date_from ?></span> to <span><?= $insights->date_until ?></span></div> 

<div class="results">
	<?php foreach ($insights->data AS $row) : ?>
	<?php foreach ($row AS $key) : ?>

	<h3>Result</h3>
	<table cellpadding="0" cellspacing="0">
		<thead>
			<tr>
				<th width="100">Item</th>
				<th>Value</th>
			</tr>
		</thead>
		<tbody>
			<tr>
				<td>ID</td>
				<td><?= $key['id'] ?></td>
			</tr>

			<tr>
				<td>Name</td>
				<td><?= $key['name'] ?></td>
			</tr>

			<tr>
				<td>Period</td>
				<td><?= $key['period'] ?></td>
			</tr>

			<tr>
				<td>Title</td>
				<td><?= $key['title'] ?></td>
			</tr>

			<tr>
				<td>Description</td>
				<td><?= $key['description'] ?></td>
			</tr>

			<?php if (isset($key['values']) AND is_array($key['values'])) : ?>
			<tr>
				<td colspan="2" class="sub">Values</td>
			</tr>
			<tr>
				<td colspan="2" class="subtable">
					<table>
						<thead>
							<tr>
								<th>Value</th>
								<th>End Time</th>
							</tr>
						</thead>

						<tbody>
							<?php foreach ($key['values'] AS $value) : ?>

							<tr>
								<td><?= $value['value'] ?></td>
								<td><?= $value['end_time'] ?></td>
							</tr>

							<?php endforeach ?>
						</tbody>
					</table>
				</td>
			</tr>
			<?php endif; ?>

		</tbody>
	</table>

	<?php endforeach; ?>
	<?php endforeach; ?>
</div>