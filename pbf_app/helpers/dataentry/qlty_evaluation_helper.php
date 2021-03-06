<?php

function qlty_evaluation($params){
	
	$ci = get_instance();
	$permissions = $ci->session->userdata('usergroupsrules');
        $canValidate = array_search('datafiles/validate/',$permissions);
		if($params['datafile_id']){
		$file_status = $ci->datafiles_mdl->get_datafile_status($params['datafile_id']);
    	$datafile_status = $file_status['datafile_valid_reg'];
  	
		}else{
			$datafile_status =0;
		}
		
	
        $mod_title = 'SAISIE DES DONNEES - '.$params['filetype_name'];
        
        $template= '<div class="block">
			
                    <div class="block_head">										
                        <h2>'.$mod_title
                            .'
                        </h2>					
                    </div>
                    <div class="block_content">
                    <form action="" name="frm_step_two" id="frm_step_two" method="post" accept-charset="utf-8">';
	$template.= '<table width="100%" border="0" cellspacing="0" cellpadding="0" class="dataentry">
            <tr>
                <td><strong>'.$ci->lang->line('list_entity').'</strong></td>
                <td>'.$params['entity_name'].'</td>
                <td rowspan="3" valign="top">'.form_textarea(	array( 	'name' => 'datafile_remark',
                                                                                                    'id' => 'datafile_remark',
                                                                                                    'class' =>'span12',
                                                                                                    'value' => $params['datafile_remark'],
                                                                                                    'rows' => 4,
                                                                                                    'cols' => 60)).
                '</td>
              </tr>
              <tr>
                    <td><strong>'.$ci->lang->line('datafile_period').'</strong></td>
                    <td>'.$ci->lang->line('app_month_'.$params['datafile_month']).' '.$params['datafile_year'].'</td>
              </tr>
  
             <tr>
                    <td><strong>Score Obtenues:</strong></td>
                    <td><input type="text" class="dataentry" id="datafile_total" value="'.(!is_null($params['datafile_total'])?number_format($params['datafile_total'],2):'').'" name="datafile_total"></td>
             </tr> 
             <tr>
                <td colspan="3">
            ';
  
  	$total_attrib = 0;
	$total_tarif = 0;
	
	$ci->table->set_template(	array ( 'table_open' => '<table class="table table-condensed">',
										'table_close' => '</table>' )
										);
			
	$ci->table->set_heading(	array(	'#',
										$ci->lang->line('datafile_indicator'),
										$ci->lang->line('indicator_attributed_point'),
										$ci->lang->line('indicator_tarif'),
										$ci->lang->line('indicator_percentage')
										));
	
	
	$lang=$ci->config->item('language_abbr');
      $sql = "SELECT pbf_indicators.indicator_id,pbf_indicators.indicator_editable_tarif,pbf_datafiledetails.datafiledetail_id, pbf_indicators.indicator_vartype,pbf_indicatorstranslations.indicator_title,pbf_datafiledetails.indicator_claimed_value,pbf_datafiledetails.indicator_verified_value,pbf_datafiledetails.indicator_validated_value,IF(pbf_datafiledetails.indicator_tarif IS NULL,IF(pbf_indicatorstarif.indicatortarif_tarif IS NULL,pbf_indicatorsfileypes.default_tarif,pbf_indicatorstarif.indicatortarif_tarif),pbf_datafiledetails.indicator_tarif) as indicator_tarif,pbf_datafiledetails.indicator_montant,pbf_indicatorsfileypes.indicator_category_id, pbf_lookups.lookup_id, pbf_lookups.lookup_title FROM pbf_indicators LEFT JOIN pbf_datafiledetails ON (pbf_indicators.indicator_id=pbf_datafiledetails.indicator_id AND pbf_datafiledetails.datafile_id='".$params['datafile_id']."') LEFT JOIN pbf_indicatorsfileypes ON (pbf_indicatorsfileypes.indicator_id=pbf_indicators.indicator_id) LEFT JOIN pbf_entities ON (pbf_entities.entity_id='".$params['entity_id']."') LEFT JOIN pbf_indicatorstranslations ON pbf_indicatorstranslations.indicator_id=pbf_indicators.indicator_id LEFT JOIN pbf_indicatorstarif ON
		  (pbf_indicatorstarif.indicatortarif_monthto>='".$params['datafile_month']."'
		    AND pbf_indicatorstarif.indicatortarif_monthfrom<='".$params['datafile_month']."'
	  AND pbf_indicatorstarif.indicatortarif_year='".$params['datafile_year']."' AND ((pbf_indicatorstarif.indicatortarif_entity_id=pbf_entities.entity_id AND pbf_indicatorstarif.indicatortarif_geozone_id=pbf_entities.entity_geozone_id) AND (pbf_indicatorstarif.indicatortarif_entity_type_id=pbf_entities.entity_type AND pbf_indicatorstarif.indicatortarif_entity_class_id=pbf_entities.entity_class)) AND pbf_indicatorstarif.indicatortarif_filetype_id=pbf_indicatorsfileypes.filetype_id)
	  LEFT JOIN pbf_lookups ON pbf_lookups.lookup_id=pbf_indicatorsfileypes.indicator_category_id
		   WHERE pbf_indicatorsfileypes.filetype_id='".$params['filetype_id']."' AND (LAST_DAY('".$params['datafile_year']."-".$params['datafile_month']."-1') BETWEEN pbf_indicatorsfileypes.use_from AND pbf_indicatorsfileypes.use_to) AND pbf_indicatorstranslations.indicator_language ='".$lang."' ORDER BY pbf_indicatorsfileypes.order";
        //echo $sql;
       // $details = $ci->datafiles_mdl->get_datafile_details($params['datafile_id'],$params['filetype_id'],$params['entity_id'],$params['datafile_month'],$params['datafile_year']);
		$details = $ci->db->query($sql)->result_array();
		
		if(empty($details)){//verification si il ya des indicateurs pour cette periode
			
			$ci->session->set_flashdata(array('mod_clss' => 'errormsg', 'mod_msg' => $ci->lang->line('datafile_missing_details'))); 
			//$ci->pbf->set_eventlog();
			//redirect('datafiles/datamngr/');
			redirect('dashboard/');
			
			}
        
        
         // only for quality files - TO DO search for quality type
        if (in_array($params['filetype_id'],array(3,4,5,6,7,8,9,20,21,22,23,24,25))){
            if (!isset($_COOKIE["info"])) {
             $data['info']=$ci->lang->line('info');

            }
        }

        $i=0;
  
        foreach ($details AS $detail){
         //	json_decode sur le champ ï¿½dataelttarif_tarifï¿½ pour avoir un array() de type dataelt_id => tarif_value


            if ($detail['indicator_tarif']!=NULL) {
                $temp = json_decode($detail['indicator_tarif'], true);
                if (is_array($temp)) {   /// json tarif, keep only the one for the correct indicator
                    
                    $details[$i]['indicator_tarif']=$temp[$detail['indicator_id']];
                }
    
                $i++;
            }
        }
        
        
	$results = $details;
	//print_test($results);
	foreach($results as $k => $result){
		if($result['indicator_editable_tarif']){
			$class_edit='editable';
		}else{
			$class_edit='not_editable';
		}
		$ci->table->add_row(	
						array('data' => $k+1, 'align' => 'right'),
						array('data' => mb_strtoupper ($result['indicator_title']).form_hidden(	array(	'indicator_id[]' => $result['indicator_id'],
																						'datafiledetail_id[]' => $result['datafiledetail_id'])
																					)),
						array('data' => form_input(	array( 	'name' => 'indicator_verified_value[]',
															'id' => 'indicator_verified_value_'.$k,
															'class'=>'dataentry',
															'value'=> (is_null($result['indicator_verified_value']) && !is_null($result['datafiledetail_id']))?'-':(!is_null($result['indicator_verified_value'])?number_format($result['indicator_verified_value'],2):''))
											)),
						array('data' => form_input(	array( 	'name' => 'indicator_tarif[]',
															'id' => 'indicator_tarif_'.$k,
															'class'=>'dataentry '.$class_edit,
															'disabled'=>'disabled',
															'value' => number_format($result['indicator_tarif'],2))
											)),
						array('data' => form_input(	array( 	'name' => 'indicator_montant[]',
															'id' => 'indicator_montant_'.$k,
															'class'=>'dataentry',
															'disabled'=>'disabled',
															'value' => (is_null($result['indicator_verified_value']) && !is_null($result['datafiledetail_id']))?'-':(!is_null($result['indicator_montant'])?number_format($result['indicator_montant'],2):''))
											))
				
						
							);
		
		$total_attrib += $result['indicator_verified_value'];
		$total_tarif += (is_null($result['indicator_verified_value']) && !is_null($result['datafiledetail_id']))?0:$result['indicator_tarif'];
		}
		
		$ci->table->add_row(	
						array('data' => '', 'align' => 'right'),
						array('data' => '<strong>Total</strong>'),
						array('data' => form_input(	array( 	'name' => 'attributed_tot',
                                                                                        'id' => 'attributed_tot',
                                                                                        'class'=>'dataentry',
                                                                                        'readonly'=>'readonly',
                                                                                        'value' => number_format($total_attrib,2))
											)),
						array('data' => form_input(	array( 	'name' => 'available_tot',
                                                                                        'id' => 'available_tot',
                                                                                        'class'=>'dataentry',
                                                                                        'readonly'=>'readonly',
                                                                                        'value' => number_format($total_tarif,2))
											)),
						array('data' => form_input(	array( 	'name' => 'datafile_ttl',
                                                                                        'id' => 'datafile_ttl',
                                                                                        'class'=>'dataentry',
                                                                                        'readonly'=>'readonly',
                                                                                        'value' => number_format(($total_attrib/$total_tarif)*100,2))
											))
				
						
							);
	$validation_button="";
	$canValidate=false;
	 if((isset($canValidate) && $canValidate)) {
               
               $validation_button.= ($datafile_status==0)? form_button($ci->lang->line('validate'), $ci->lang->line('validate'), 'id="validate"'):
                form_button($ci->lang->line('unvalidate'), $ci->lang->line('unvalidate'), 'id="validate"');           
                
            }								
		
	return $template.$ci->table->generate().form_hidden(	array(	'datafile_id' => $params['datafile_id'],
																	'filetype_id' => $params['filetype_id'],
																	'entity_id' => $params['entity_id'],
																	'datafile_month' => $params['datafile_month'],
																	'datafile_year' => $params['datafile_year'])).
                '</td>
                            </tr>
                            <tr>
                                <td colspan="3">'.form_button('save', $ci->lang->line('app_form_save'), 'onClick="savefile()" id="save"').$validation_button.
                                    form_button('cancel',$ci->lang->line('app_form_cancel'),'onClick="history.go(-1);return true;"').
                                '</td>
                            </tr>
                        </table>
                   </form>
                   </div>
               </div>'.
'<script type="text/javascript">
	$(document).ready(function() {
		$("[id*=datafile_total]").blur(function() {
			$(this).parseNumber({
				format: "#,###.00",
				locale: "us"
			});
			$(this).formatNumber({
				format: "#,###.00",
				locale: "us"
			});
		});
		
		////
		$("[id*=indicator_verified_value_]").blur(function() {
			var row_nbr = this.id.split("_");
			var str_len = row_nbr.length;
			row_nbr = row_nbr[parseInt(row_nbr.length) - 1];
			var attrib_tot = 0;
			var available_tot = 0;
			var attrib = $("#indicator_verified_value_" + row_nbr).val().replace(/,/g, "");
			var available = $("#indicator_tarif_" + row_nbr).val().replace(/,/g, "");
			if (parseFloat(attrib) > parseFloat(available)) {
				$("#indicator_verified_value_" + row_nbr).val(0);
				$("#indicator_montant_" + row_nbr).val(0);
			} else if (isNaN(attrib) || attrib == "-") {
				$("#indicator_verified_value_" + row_nbr).val("-");
				$("#indicator_montant_" + row_nbr).val("-");
				$("#available_tot").val(parseFloat($("#available_tot").val().replace(/,/g, "")) - parseFloat($("#indicator_tarif_" + row_nbr).val().replace(/,/g, "")));
			} else {
				$("#indicator_montant_" + row_nbr).val(Math.round(((attrib / available) * 100) * 100) / 100);
				$(this).parseNumber({
					format: "#,###.00",
					locale: "us"
				});
				$(this).formatNumber({
					format: "#,###.00",
					locale: "us"
				});
				$("#indicator_montant_" + row_nbr).parseNumber({
					format: "#,###.00",
					locale: "us"
				});
				$("#indicator_montant_" + row_nbr).formatNumber({
					format: "#,###.00",
					locale: "us"
				});
				$("input[name=\"indicator_montant[]\"]").each(function(i) {
					if (!isNaN($("#indicator_verified_value_" + i).val().replace(/,/g, "")) && $("#indicator_verified_value_" + i).val() != "") {
						attrib_tot += parseFloat($("#indicator_verified_value_" + i).val().replace(/,/g, ""));
					}
					if (!isNaN($("#indicator_verified_value_" + i).val().replace(/,/g, "")) && !isNaN($("#indicator_tarif_" + i).val().replace(/,/g, "")) && $("#indicator_tarif_" + i).val() != "") {
						available_tot += parseFloat($("#indicator_tarif_" + i).val().replace(/,/g, ""));
					}
					$("#attributed_tot").val(attrib_tot);
					$("#available_tot").val(available_tot);
					$("#datafile_ttl").val(Math.round(((attrib_tot / available_tot) * 100) * 100) / 100);
					$("#available_tot").parseNumber({
						format: "#,###.00",
						locale: "us"
					});
					$("#available_tot").formatNumber({
						format: "#,###.00",
						locale: "us"
					});
					$("#datafile_ttl").parseNumber({
						format: "#,###.00",
						locale: "us"
					});
					$("#datafile_ttl").formatNumber({
						format: "#,###.00",
						locale: "us"
					});
				});
			}
		});
		
	
		
		$("[id*=indicator_tarif_]").blur(function() {
			var row_nbr = this.id.split("_");
			var str_len = row_nbr.length;
			row_nbr = row_nbr[parseInt(row_nbr.length) - 1];
			var attrib_tot = 0;
			var available_tot = 0;
			var attrib = $("#indicator_verified_value_" + row_nbr).val().replace(/,/g, "");
			var available = $("#indicator_tarif_" + row_nbr).val().replace(/,/g, "");
			if (parseFloat(attrib) > parseFloat(available)) {
				$("#indicator_verified_value_" + row_nbr).val(0);
				$("#indicator_montant_" + row_nbr).val(0);
			} else if (isNaN(attrib) || attrib == "-") {
				$("#indicator_verified_value_" + row_nbr).val("-");
				$("#indicator_montant_" + row_nbr).val("-");
				$("#available_tot").val(parseFloat($("#available_tot").val().replace(/,/g, "")) - parseFloat($("#indicator_tarif_" + row_nbr).val().replace(/,/g, "")));
			} else {
				$("#indicator_montant_" + row_nbr).val(Math.round(((attrib / available) * 100) * 100) / 100);
				$(this).parseNumber({
					format: "#,###.00",
					locale: "us"
				});
				$(this).formatNumber({
					format: "#,###.00",
					locale: "us"
				});
				$("#indicator_montant_" + row_nbr).parseNumber({
					format: "#,###.00",
					locale: "us"
				});
				$("#indicator_montant_" + row_nbr).formatNumber({
					format: "#,###.00",
					locale: "us"
				});
				
				$("#indicator_verified_value_" + row_nbr).parseNumber({
					format: "#,###.00",
					locale: "us"
				});
				$("#indicator_verified_value_" + row_nbr).formatNumber({
					format: "#,###.00",
					locale: "us"
				});
				$("input[name=\"indicator_montant[]\"]").each(function(i) {
					if (!isNaN($("#indicator_verified_value_" + i).val().replace(/,/g, "")) && $("#indicator_verified_value_" + i).val() != "") {
						attrib_tot += parseFloat($("#indicator_verified_value_" + i).val().replace(/,/g, ""));
					}
					if (!isNaN($("#indicator_verified_value_" + i).val().replace(/,/g, "")) && !isNaN($("#indicator_tarif_" + i).val().replace(/,/g, "")) && $("#indicator_tarif_" + i).val() != "") {
						available_tot += parseFloat($("#indicator_tarif_" + i).val().replace(/,/g, ""));
					}
					$("#attributed_tot").val(attrib_tot);
					$("#available_tot").val(available_tot);
					$("#datafile_ttl").val(Math.round(((attrib_tot / available_tot) * 100) * 100) / 100);
					$("#available_tot").parseNumber({
						format: "#,###.00",
						locale: "us"
					});
					$("#available_tot").formatNumber({
						format: "#,###.00",
						locale: "us"
					});
					$("#datafile_ttl").parseNumber({
						format: "#,###.00",
						locale: "us"
					});
					$("#datafile_ttl").formatNumber({
						format: "#,###.00",
						locale: "us"
					});
				});
			}
		});
		
		
	});

	function savefile() {
		if (parseFloat($("#datafile_total").val().replace(/,/g, "")) == parseFloat($("#datafile_ttl").val().replace(/,/g, ""))) {
			$("input[name=\"indicator_montant[]\"]").each(function(i) {
				$("#indicator_montant_" + i).removeAttr("disabled");
				$("#indicator_tarif_" + i).removeAttr("disabled");
			});
			document.getElementById("frm_step_two").action = "'.base_url().'datafiles/save";
			document.getElementById("frm_step_two").submit();
		} else {
			alert("'.$ci->lang->line('datafile_check_totals').'");
			return false;
		}
	}
</script>
<script>    
    $(function(){
    
        if ($("#datafile_total").val() == "" ) {
            
            $("input[name=\'indicator_claimed_value[]\']").each(function(){
            $(this).attr("disabled","disabled");
            });
            $("input[name=\'indicator_validated_value[]\']").each(function(){
            $(this).attr("disabled","disabled");
            });
            $("input[name=\'indicator_verified_value[]\']").each(function(){
            $(this).attr("disabled","disabled");
            });
            $("input[name=\'indicator_tarif[]\']").each(function(){
            $(this).attr("disabled","disabled");
            });
        }else{
        	$(".editable").each(function(){
      			$(this).removeAttr("disabled");
			});
			
        }
		
		
		
		
		var status = '.$datafile_status.';
       
        
        var fileId = '.$ci->uri->segment(2).';
        var siteUrl = "'.site_url().'datafiles/validate/'.$ci->uri->segment(2).'";
       
     
        if(status!=0) {
            
            $("input").attr("disabled","disabled");
            $("#save").hide();
            siteUrl+="/0";
        }
        
        if(fileId==0) {
            $("#validate").hide();
        }
        
        $("#validate").click(function(){
            console.log("site url"+siteUrl);
            $.ajax({
                url : siteUrl,
                success : function(){
                    window.location.href ="'.site_url('datafiles/datamngr').'";
                },
                error : function() {
                    window.location.href ="'.site_url('datafiles/datamngr').'";
                }
            });
        });
		
		
		
        
        $("input").on("keydown", function(e) {
	 if (e.keyCode==13) {
		if(this.id == "datafile_total"){
			
			$("input[name=\'indicator_verified_value[]\']").eq(0).focus();
                        $("input[name=\'indicator_claimed_value[]\']").eq(0).focus();
		}
		else{
	  		var focusable = $("input").filter(":visible:enabled");
	 		focusable.eq(focusable.index(this)+1).focus();
			}
	  return false;
	 }
	});
        
        $("#datafile_total").on("blur", function(e) {
		
		if($("#datafile_total").val() != "" && !isNaN($("#datafile_total").val().replace(/,/g,""))){

			$("input[name=\'indicator_claimed_value[]\']").each(function(){
      			$(this).removeAttr("disabled");
			});
			$("input[name=\'indicator_validated_value[]\']").each(function(){
      			$(this).removeAttr("disabled");
			});
			$("input[name=\'indicator_verified_value[]\']").each(function(){
      			$(this).removeAttr("disabled");
			});
			
			$(".editable").each(function(){
      			$(this).removeAttr("disabled");
			});
			
			$("input[name=\'indicator_validated_value[]\']").eq(0).focus();
		}
                else{
		
			$("input[name=\'indicator_claimed_value[]\']").each(function(){
      			$(this).attr("disabled","disabled");
			});
			$("input[name=\'indicator_validated_value[]\']").each(function(){
      			$(this).attr("disabled","disabled");
			});
			$("input[name=\'indicator_verified_value[]\']").each(function(){
      			$(this).attr("disabled","disabled");
			});
		
			$(".editable").each(function(){
      			$(this).attr("disabled","disabled");
			});
		
		}
	});
        
    });
</script>
';
	
	}

?>