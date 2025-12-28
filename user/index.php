<?php
session_start();
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Online Ticket System - CS306 Phase 3</title>
</head>
<body>

<h1>Online Ticket System - CS306 Phase 3</h1>

<hr>

<h2 id="triggers">Triggers:</h2>

<div style="border:1px solid #000; padding:12px; margin-bottom:14px;">
  <b>Trigger 1 (by Berkay Bilici):</b> This trigger automatically updates inventory levels by deducting the purchased quantity from the product stock immediately after a new order item is recorded.<br>
  <a href="triggers/after_orderdetail_insert_stock.php">Go to the trigger's page</a>
</div>

<div style="border:1px solid #000; padding:12px; margin-bottom:14px;">
  <b>Trigger 2 (by Yağmur Geçim):</b> trigger 2: we can insert product to orderdetail table, we are able to choose an order from previous orders. the total amount is changed in orderdetail and order tables.<br>
  <a href="triggers/after_orderdetail_update_total.php">Go to the trigger's page</a>
</div>

<div style="border:1px solid #000; padding:12px; margin-bottom:14px;">
  <b>Trigger 3 (by Sinan Altıntuğ):</b> Prevents inserting products with negative prices and displays an error message.<br>
  <a href="triggers/add_product.php">Go to the trigger's page</a>
</div>

<hr>

<h2 id="procedures">Stored Procedures:</h2>

<div style="border:1px solid #000; padding:12px; margin-bottom:14px;">
  <b>Stored Procedure 1 (by Berkay Bilici):</b> This stored procedure safely registers a new customer by first checking if the username already exists, preventing duplicate records in the database.<br>
  <a href="procedures/sp_add_customer_safe.php">Go to the procedure's page</a>
</div>

<div style="border:1px solid #000; padding:12px; margin-bottom:14px;">
  <b>Stored Procedure 2 (by Yağmur Geçim):</b>  we are able to insert a product directly by using stored product. in that part we can add a product by using its product_id, to a choosen order and choosing quantity. total amount is changed in order table.<br>
  <a href="procedures/sp_add_order_item.php">Go to the procedure's page</a>
</div>

<div style="border:1px solid #000; padding:12px; margin-bottom:14px;">
  <b>Stored Procedure 3 (by Sinan Altıntuğ):</b> Applies a percentage discount to all products in a selected category (product_type). Rejects negative discount rates.<br>
  <a href="procedures/sp_apply_discount_by_category.php">Go to the procedure's page</a>
</div>

<ul>
  <li><a href="tickets/tickets.php">Support Tickets</a></li>
</ul>

<hr>

<a href="../admin/index.php">Admin Panel</a>

</body>
</html>
