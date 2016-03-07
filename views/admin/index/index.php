<?php

echo head($head);
echo flash();

?>
<div>
  <a class="redirect" href="<?php echo url("redirect/index/add"); ?>">Add a Redirect</a>
</div>
<table>
  <thead>
    <tr>
      <th>Source</th>
      <th>Redirect</th>
      <th>Count</th>
      <th>Last Accessed</th>
      <th>Operations</th>
    </tr>
  </thead>
  <tbody>
    <?php while ($redirect = $redirects->fetchObject()): ?>
        <tr>
          <td>
            <a target="_blank" href="/<?php echo $redirect->source; ?>"><?php echo $redirect->source; ?></a>
          </td>
          <td>
            <a href="/<?php echo $redirect->redirect; ?>"><?php echo $redirect->redirect; ?></a>
          </td>
          <td>
            <?php echo $redirect->count; ?>
          </td>
          <td>
            <?php echo $redirect->access ? format_interval(time() - $redirect->access) . ' ago' : 'Never'; ?>
          </td>
          <td>
            <a href="<?php echo url("redirect/index/edit/id/{$redirect->id}");?>">edit</a>
          </td>
        </tr>
    <?php endwhile; ?>
  </tbody>
</table>

<?php echo foot();
