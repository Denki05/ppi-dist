<?php 
	$total = 0;
?>
<!DOCTYPE html>
<html>
<head>
	<title></title>
	<style type="text/css">
		tbody{
			font-size: 12px;
		}
		/*Table*/
		table.table-data, .table-data td, .table-data th {
		  border: 1px solid #333;
		}
		table.table-data th{
		  font-size: 12px;
		}

		table.table-data {
		  width: 100%;
		  border-collapse: collapse;
		  color: #333;
		}
		table.table-data tbody{
		  text-align: center;
		  font-size: 12px;
		}
		.text-center{
			text-align: center;
		}
		.text-right{
			text-align: right;
		}
		.text-left{
			text-align: left;
		}

		.column-float {
		  float: left;
		  width: 50%;
		}

		/* Clear floats after the columns */
		.row-float:after {
		  content: "";
		  display: table;
		  clear: both;
		}

		#footer {
		  position: fixed;
		  left: 0;
		  right: 0;
		  color: #aaa;
		  font-size: 11px;
		  bottom: 0;
		}
		
		.page-number:before {
		  content: "Print Manifest | Page " counter(page);
		}

	</style>
</head>
<body>
	<div class="row-float" style="width: 100%;">
	  <div class="column-float note" style="width: 49%;">
	      <div style="margin-bottom: 0px;">
	        <table style="width: 100%;">
	          <tbody>
	            <tr>
	              <td style="width: 10% !important;"><strong>Customer</strong></td>
	              <td style="width: 2% !important;">:</td>
	              <td style="width: 40% !important;">{{$result->customer->name ?? ''}}</td>
	              <td style="width: 6% !important;"></td>
	              <td style="width: 10% !important;" ><strong>Ekspedisi</strong></td>
	              <td style="width: 2% !important;" >:</td>
	              <td style="width: 30% !important;"><div style="word-wrap: break-word;">{{$result->ekspedisi->name ?? null}}</div></td>
	            </tr>
	            <tr>
	              <td style="width: 10% !important;"><strong>Address</strong></td>
	              <td style="width: 2% !important;">:</td>
	              <td style="width: 40% !important;"><div style="word-wrap: break-word;">{{$result->customer->address ?? ''}}</div></td>
	              <td style="width: 6% !important;"></td>
	              <td style="width: 10% !important;"><strong>Delivery</strong></td>
	              <td style="width: 2% !important;">:</td>
	              <td style="width: 30% !important;"><div style="word-wrap: break-word;">{{$result->customer_other_address->label ?? ''}}</div></td>
	            </tr>
	            <tr>
	              <td style="width: 10% !important;"><strong>Phone</strong></td>
	              <td style="width: 2% !important;">:</td>
	              <td style="width: 40% !important;"><div style="word-wrap: break-word;">{{$result->customer->phone ?? ''}}</div></td>
	              <td style="width: 6% !important;"></td>
	              <td style="width: 10% !important;"><strong>Address</strong></td>
	              <td style="width: 2% !important;">:</td>
	              <td style="width: 30% !important;"><div style="word-wrap: break-word;">{{$result->customer_other_address->address ?? ''}}</div></td>
	            </tr>
	            <tr>
	              <td style="width: 10% !important;"></td>
	              <td style="width: 2% !important;"></td>
	              <td style="width: 40% !important;"></td>
	              <td style="width: 6% !important;"></td>
	              <td style="width: 10% !important;"><strong>Phone</strong></td>
	              <td style="width: 2% !important;">:</td>
	              <td style="width: 30% !important;"><div style="word-wrap: break-word;">{{$result->customer_other_address->phone ?? ''}}</div></td>
	            </tr>
	          </tbody>
	        </table>
	      </div>
	  </div>
	  <div class="column-float note" style="width: 2%;">
	      
	  </div>
	  <div class="column-float" style="width: 49%;">
	      <div style="margin-bottom: 0px;">
	        <table style="width: 100%;">
	          <tbody>
	            <tr>
	              <td style="width: 10% !important;"><strong>Customer</strong></td>
	              <td style="width: 2% !important;">:</td>
	              <td style="width: 40% !important;">{{$result->customer->name ?? ''}}</td>
	              <td style="width: 6% !important;"></td>
	              <td style="width: 10% !important;" ><strong>Ekspedisi</strong></td>
	              <td style="width: 2% !important;" >:</td>
	              <td style="width: 30% !important;"><div style="word-wrap: break-word;">{{$result->ekspedisi->name ?? null}}</div></td>
	            </tr>
	            <tr>
	              <td style="width: 10% !important;"><strong>Address</strong></td>
	              <td style="width: 2% !important;">:</td>
	              <td style="width: 40% !important;"><div style="word-wrap: break-word;">{{$result->customer->address ?? ''}}</div></td>
	              <td style="width: 6% !important;"></td>
	              <td style="width: 10% !important;"><strong>Delivery</strong></td>
	              <td style="width: 2% !important;">:</td>
	              <td style="width: 30% !important;"><div style="word-wrap: break-word;">{{$result->customer_other_address->label ?? ''}}</div></td>
	            </tr>
	            <tr>
	              <td style="width: 10% !important;"><strong>Phone</strong></td>
	              <td style="width: 2% !important;">:</td>
	              <td style="width: 40% !important;"><div style="word-wrap: break-word;">{{$result->customer->phone ?? ''}}</div></td>
	              <td style="width: 6% !important;"></td>
	              <td style="width: 10% !important;"><strong>Address</strong></td>
	              <td style="width: 2% !important;">:</td>
	              <td style="width: 30% !important;"><div style="word-wrap: break-word;">{{$result->customer_other_address->address ?? ''}}</div></td>
	            </tr>
	            <tr>
	              <td style="width: 10% !important;"></td>
	              <td style="width: 2% !important;"></td>
	              <td style="width: 40% !important;"></td>
	              <td style="width: 6% !important;"></td>
	              <td style="width: 10% !important;"><strong>Phone</strong></td>
	              <td style="width: 2% !important;">:</td>
	              <td style="width: 30% !important;"><div style="word-wrap: break-word;">{{$result->customer_other_address->phone ?? ''}}</div></td>
	            </tr>
	          </tbody>
	        </table>
	      </div>
	  </div>
	</div>
	<div>
		<div style="width: 100%;">
			<table style="width: 100%;">
				<tr>
					<td style="width: 49%;">
						<table style="width: 100%;" class="table-data">
						  <thead>
						    <tr>
						      <th style="padding: 6px 2px !important;">Product</th>
						      <th style="padding: 6px 2px !important;">Code</th>
						      <th style="padding: 6px 2px !important;">Qty</th>
						      <th style="padding: 6px 2px !important;">Unit</th>
						      <th style="padding: 6px 2px !important;">Packing</th>
						      <th style="padding: 6px 2px !important;">Total Packing</th>
						      <th style="padding: 6px 2px !important;">Checker</th>
						    </tr>
						  </thead>
						  <tbody>
						    @foreach($result_item as $index => $row)
						      <tr >
						        <td style="padding: 5px 2px !important;">{{$row->product->name ?? ''}}</td>
						        <td style="padding: 5px 2px !important;">{{$row->product->code ?? ''}}</td>
						        <td style="padding: 5px 2px !important;">{{$row->qty}}</td>
						        <td style="padding: 5px 2px !important;">Kg</td>
						        <td style="padding: 5px 2px !important;">{{$row->packaging_txt()->scalar ?? ''}}</td>
						        <td style="padding: 5px 2px !important;">
						          @if($row->packaging == 7)
						          Free
						          @else
						          <?php
						            $total_packing = $row->qty / floatval($row->packaging_val()->scalar ?? 0);
						            $total += $total_packing;
						          ?>
						          {{$total_packing}}
						          @endif
						        </td>
						        <td style="padding: 5px 2px !important;"></td>
						      </tr>
						    @endforeach
						    <tr>
						      <td colspan="5" class="text-right" style="padding: 5px 2px !important;">Total</td>
						      <td style="padding: 5px 2px !important;">{{$total}}</td>
						      <td style="padding: 5px 2px !important;"></td>
						    </tr>
						  </tbody>
						</table>
					</td>
					<td style="width: 2%;"></td>
					<td style="width: 49%;">
						<table style="width: 100%;" class="table-data">
						  <thead>
						    <tr class="text-center">
						      <th style="padding: 6px 2px !important;">Product</th>
						      <th style="padding: 6px 2px !important;">Code</th>
						      <th style="padding: 6px 2px !important;">Qty</th>
						      <th style="padding: 6px 2px !important;">Unit</th>
						      <th style="padding: 6px 2px !important;">Packing</th>
						      <th style="padding: 6px 2px !important;">Total Packing</th>
						    </tr>
						  </thead>
						  <tbody>
						    @foreach($result->do_detail as $index => $row)
						      <tr class="text-center" >
						        <td style="padding: 5px 2px !important;">{{$row->product->name ?? ''}}</td>
						        <td style="padding: 5px 2px !important;">{{$row->product->code ?? ''}}</td>
						        <td style="padding: 5px 2px !important;">{{$row->qty}}</td>
						        <td style="padding: 5px 2px !important;">Kg</td>
						        <td style="padding: 5px 2px !important;">{{$row->packaging_txt()->scalar ?? ''}}</td>
						        <td style="padding: 5px 2px !important;">
						          @if($row->packaging == 7)
						          Free
						          @else
						          <?php
						            $total_packing = $row->qty / floatval($row->packaging_val()->scalar ?? 0);
						          ?>
						          {{$total_packing}}
						          @endif
						        </td>
						      </tr>
						    @endforeach
						    <tr class="text-center">
						      <td colspan="5" class="text-right" style="padding: 5px 2px !important;">Total</td>
						      <td style="padding: 5px 2px !important;">{{$total}}</td>
						    </tr>
						  </tbody>
						</table>
					</td>
				</tr>
			</table>
		</div>
	</div>

	<div id="footer">
	  <div class="page-number"></div>
	</div>
</body>
</html>