<html>
<?php
  if ($_GET['action'] == 'approve')
  {
    update_user_meta($_GET['id'], 'status', 'approved');
  }
  if ($_GET['action'] == 'delete')
  {
    wp_delete_user($_GET['id']);
  }
?>
    
<div class="row">
    <div >
        <h2>Notifications that await confirmation</h2>
            <div >
                <table style = "width: 98%" class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>
                                First name
                            </th>
                            <th>
                              Second name
                            </th>
                            <th>
                                Plan
                            </th>
                            <th>
                                Phone number
                            </th>
                            <th style = "width: 5%">
                                
                            </th>
                            <th style = "width: 5%">
                                
                            </th>
                        </tr>
                    </thead>
                    <tbody> 
                      <?php
                      global $wpdb;
              $user_id_plan = $wpdb->get_results( "SELECT user_id, meta_value FROM wp_usermeta WHERE meta_value = 'not aproved'" );
              foreach ($user_id_plan as $user) 
              {
                $first_name = $wpdb->get_row( "SELECT meta_value FROM wp_usermeta WHERE meta_key = 'first_name' AND user_id = '$user->user_id'" );
                $last_name = $wpdb->get_row(  "SELECT meta_value FROM wp_usermeta WHERE meta_key = 'last_name' AND user_id = '$user->user_id'" );
                $phone = $wpdb->get_row(      "SELECT meta_value FROM wp_usermeta WHERE meta_key = 'phone' AND user_id = '$user->user_id'" );
                $plan = $wpdb->get_row(       "SELECT meta_value FROM wp_usermeta WHERE meta_key = 'plan' AND user_id = '$user->user_id'" );
                echo "<tr>";
                echo "
                  <td>$first_name->meta_value</td>
                  <td> $last_name->meta_value</td>
                  <td>     $plan->meta_value</td>
                  <td>     $phone->meta_value</td>
                  <td>    $status->meta_value</td>";
                echo "
                      <td >
                          <a class=\"btn btn-success\" href=\"admin.php?page=pd_subscriptions_plans%2Fnotifications.php&id=$user->user_id&action=approve\">Approve</a>
                        </td>
                        <td >
                          <a class=\"btn btn-danger\" href=\"admin.php?page=pd_subscriptions_plans%2Fnotifications.php&id=$user->user_id&action=delete\">Delete</a>
                        </td>
                      </tr>";
                }  
              ?>                              
                    </tbody>
                </table>

