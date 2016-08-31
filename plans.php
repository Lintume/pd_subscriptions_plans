<html>    
<?php
    if ($_GET['id'])//delete plan
    {
        global $wpdb;
        $table_name = $wpdb->prefix . "plans";

        $myrows = $wpdb->get_results(  "SELECT meta_value FROM wp_usermeta WHERE meta_key = 'plan' AND meta_value = '$_GET[name]'" );//Search by name plan among the users plans
        foreach ($myrows as $plan) //We are looking for data users with the plans, the removal only if the plan is not tied to the user
        { 
            if ($plan->meta_value == $_GET['name'])
            {
                echo '<script>alert("Sorry, but you can not delete this plan, since it is tied to the user ")</script>';
                break;
            }                                           
        }
        if($myrows == NULL)//If no search results remove plan
        {
            $wpdb->query( "DELETE FROM `" . $table_name . "` WHERE id = '$_GET[id]';");
        }           
        unset($_GET); 
    }

    if (isset($_GET['name']))//add new plan
    {
        $name        = $_GET['name'];
        $price       = $_GET['price'];
        $description = $_GET['description'];
        $color       = $_GET['color'];

        global $wpdb;
            $table_name = $wpdb->prefix . "plans";
            $wpdb->query( "INSERT INTO `" . $table_name . "` (name, price, description, color, visibility) VALUES ('$name', '$price', '$description', '$color', 'yes')");
            unset($_GET); 
            die(json_encode(array("success"=>true)));
    }

    if (isset($_POST['val']))//change columns per row
    {
        update_option('col_per_row', $_POST['val']);
    }

    if ($_GET['plan_id'])//change status visibility by checkbox
    {
        global $wpdb;
        $wpdb->query( "UPDATE wp_plans SET visibility = 'no' WHERE id = '$_GET[plan_id]'");
        if ($_GET['status'] == 'true')
        {
            $wpdb->query( "UPDATE wp_plans SET visibility = 'yes' WHERE id = '$_GET[plan_id]'");
        }
        die(json_encode(array("success"=>true)));
    }

    if ($_GET['panels_functions'])
    {
        $_GET['panels_functions'] == 'on'? update_option('panels_functions', 'on'):update_option('panels_functions', 'off');
    }
?>

<div>
    <p></p>
    <h3>Select price box per row and functional panels</h3>
    <form action="admin.php?page=pd_subscriptions_plans%2Fplans.php" method="post">
        <p>
            <select name = 'val'>
                <option value="2">2</option>
                <option selected value="3">3</option>
                <option value="4">4</option>
                <option value="6">6</option>
           </select>
            <input type="submit" class="btn btn-primary" value="Save">
       </p>
    </form>
</div>

<?php
    $panels_functions = get_option('panels_functions');
    if($panels_functions == 'on')
    {
        echo "<a class=\"btn btn-success\" href=\"admin.php?page=pd_subscriptions_plans%2Fplans.php&panels_functions=off\">Panel's functions ON</a>";
    }
    if($panels_functions == 'off')
    {
        echo "<a class=\"btn btn-danger\" href=\"admin.php?page=pd_subscriptions_plans%2Fplans.php&panels_functions=on\">Panel's functions OFF</a>";
    }
?>

<table>
          <tr>
            <th>
              <h2>Plans</h2>
            </th>
            <th style="padding: 10px">
              <button type="button" class="btn btn-default " id="myBtn" ><span class="glyphicon glyphicon-plus"></span> </button>
            </th>
          </tr>
          </table>

<div class="row">
    <div >
        <div class="table-responsive">
            <table style = "width: 98%" class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th style = "width: 5%">
                            Visibility 
                        </th>
                        <th style = "width: 20%">
                            Name
                        </th>
                        <th style = "width: 10%">
                            Price
                        </th>
                        <th style = "width: 40%">
                            Description
                        </th>
                        <th style = "width: 10%">
                            Color
                        </th>
                        <th style="display: none">
                            ID
                        </th>
                        <th style = "width: 5%">
                            
                        </th>
                    </tr>
                    </thead>
                    <tbody id = 'checkCont'> 
                    <?php
                        global $wpdb;
                        $myrows = $wpdb->get_results( "SELECT * FROM wp_plans" );
                        //echo var_dump($myrows);
                        foreach ($myrows as $plan) 
                        {
                            $check = ($plan->visibility == "yes"? 'checked':'');//check status plan visibility: yes or no
                            echo "<tr>";
                            echo "
                                <td> <input type=\"checkbox\" data-plan_id=\"$plan->id\" $check id=\"check-succ\" value=\"\"></td>
                                <td>                           $plan->name</td>
                                <td>                           $plan->price</td>
                                <td>                           $plan->description</td>
                                <td bgcolor = $plan->color>    $plan->color</td>
                                <td style=\"display: none\">                           $plan->id</td>";
                            echo "
                                <td>
                                    <a class=\"btn btn-danger
                                    \" href=\"admin.php?page=pd_subscriptions_plans%2Fplans.php&id=$plan->id&name=$plan->name\">Delete</a>
                                </td>
                            </tr>";
                        }
                    ?>
                    </tbody>
                </table>

<html>
<head>
    <script>
$(document).ready(function(){
    $("#myBtn").click(function(){
        $("#myModal").modal();
    });

    $("#myModal form").submit(function(event)
    {
        event.preventDefault();//disable the default behavior
        $.ajax({
            url: "admin.php?page=pd_subscriptions_plans%2Fplans.php",
            method: "GET",
            data: { name: $("#name").val(), price: $("#price").val(), description: $("#description").val(), color: $("#color").val(), },
            success:function(response)
            {
                if (response.success)
                {
                    console.log("data Saved");
                    window.location.reload();
                    $('#myModal').modal('hide');
                    $("#name").val("");
                    $("#price").val("");
                    $("#description").val("");
                    $("#color").val("");
                }
                else
                {
                    console.error("Validation Error");
                }
            },
            error:function()
            {
                window.location.reload();
                console.error("Server Error");
            },
            dataType: "json"
        });
    });

    $('#checkCont').on('change', '[type=checkbox]', function(){
        event.preventDefault();
        $.ajax({
            url: "admin.php?page=pd_subscriptions_plans%2Fplans.php",
            method: "GET",
            data: { status: $(this).is(':checked'), plan_id: $(this).data('plan_id')},
            dataType: "json"
        });
    });
});
</script>

    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        .modal-header, h4, .close {
            background-color: #5cb85c;
            color:white !important;
            text-align: center;
            font-size: 30px;
        }
        .modal-footer {
            background-color: #f9f9f9;
        }
    </style>
    <link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/normalize/3.0.3/normalize.min.css">

</head>
<body>

<div class="container">

    <!-- Modal -->
    <div class="modal fade" id="myModal" role="dialog">
        <div class="modal-dialog">

            <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-header" style="padding:35px 50px;">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4>Add new plan</h4>
                </div>
                <div class="modal-body" style="padding:40px 50px;">
                    <form role="form">
                        <input class="form-control" id = "name" placeholder="Name"><br>
                        <input class="form-control" id = "price" placeholder="Price"><br>
                        <input class="form-control" id = "description" placeholder="Descripion"><br>
                        <input class="form-control" input type="text" name="duplicated-name-2" data-palette='["#D50000","#304FFE","#00B8D4","#00C853","#FFD600","#FF6D00","#FF1744","#3D5AFE","#00E5FF","#00E676","#FFEA00","#FF9100","#FF5252","#536DFE","#18FFFF","#69F0AE","#FFFF00","#FFAB40"]' id = "color" value="" style="margin-right:48px;">
                        <br>
                        <button type="submit" class="btn btn-success btn-block">Save</button>

                    </form>
                </div>
                <div class="modal-footer">
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>