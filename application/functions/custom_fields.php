<?
function get_custom_fields($table, $id = 0, $return_full = false, $field_for = false, $debug = false, $field_type = false) {
	$params = array();

	$table_assoc_name = false;
	// $id = intval ( $id );
	if (is_string($table)) {

		$params2 = parse_params($table);

		if (!is_array($params2) or (is_array($params2) and count($params2) < 2)) {

			$id = trim($id);
			$table = db_escape_string($table);

			if ($table != false) {
				$table_assoc_name = db_get_table_name($table);
			} else {

				$table_assoc_name = "MW_ANY_TABLE";
			}

			if ((int)$table_assoc_name == 0) {
				$table_assoc_name = guess_table_name($table);

			}
			if ($table_assoc_name == false) {
				$table_assoc_name = db_get_assoc_table_name($table_assoc_name);

			}
		} else {
			$params = $params2;
		}
	}

	if (isset($params['for'])) {
		$table_assoc_name = db_get_assoc_table_name($params['for']);
	}

	if (isset($params['for_id'])) {
		$id = db_escape_string($params['for_id']);
	}

	if (isset($params['field_type'])) {
		$field_type = db_escape_string($params['field_type']);
	}

	// ->'table_custom_fields';
	$table_custom_field = MW_TABLE_PREFIX . 'custom_fields';

	$the_data_with_custom_field__stuff = array();

	if (strval($table_assoc_name) != '') {

		if ($field_for != false) {
			$field_for = trim($field_for);
			$field_for_q = " and  (field_for='{$field_for} OR custom_field_name='{$field_for}')'";
		} else {
			$field_for_q = " ";
		}

		if ($table_assoc_name == 'MW_ANY_TABLE') {

			$qt = '';
		} else {
			//$qt = " (to_table='{$table_assoc_name}'  or to_table='{$table_ass}'  ) and";

			$qt = " to_table='{$table_assoc_name}'    and";
		}

		if ($return_full == true) {

			$select_what = '*';
		} else {
			$select_what = '*';
		}

		if ($field_type == false) {

			$field_type_q = ' ';
			$field_type_q = ' and custom_field_type!="content"  ';
		} elseif ($field_type == 'all') {

			$field_type_q = ' ';

		} else {
			$field_type = db_escape_string($field_type);
			$field_type_q = ' and custom_field_type="' . $field_type . '"  ';
		}

		$sidq = '';
		if ($id == 0) {
			if (is_admin() != false) {
				//$sid = session_id();
				//$sidq = ' and session_id="' . $sid . '"  ';
			}
		} else {
			$sidq = '';
		}

		$q = " SELECT
		{$select_what} from  $table_custom_field where
		{$qt}
		to_table_id='{$id}'
		$field_for_q
		$field_type_q
		$sidq
		order by position asc
		   ";

		if ($debug != false) {

		}

		// $crc = crc32 ( $q );

		$crc = (crc32($q));
 
		$cache_id = __FUNCTION__ . '_' . $crc;

		$q = db_query($q, $cache_id, 'custom_fields/global');
 
		if (!empty($q)) {

			if ($return_full == true) {
				$to_ret = array();
				$i = 1;
				foreach ($q as $it) {

					// $it ['value'] = $it ['custom_field_value'];
					$it['value'] = $it['custom_field_value'];
					if (isset($it['custom_field_value']) and strtolower($it['custom_field_value']) == 'array') {
						if (isset($it['custom_field_values']) and is_string($it['custom_field_values'])) {
							$try = base64_decode($it['custom_field_values']);

							if ($try != false and strlen($try) > 5) {
								$it['custom_field_values'] = unserialize($try);
							}
						}
					}

					//  $it['values'] = $it['custom_field_value'];

					// $it['cssClass'] = $it['custom_field_type'];
					$it['type'] = $it['custom_field_type'];
					$it['position'] = $i;
					//  $it['baseline'] = "undefined";

					$it['title'] = $it['custom_field_name'];
					$it['required'] = $it['custom_field_required'];

					$to_ret[] = $it;
					$i++;
				}
				return $to_ret;
			}

			$append_this = array();
			if (is_array($q) and !empty($q)) {
				foreach ($q as $q2) {

					$i = 0;

					$the_name = false;

					$the_val = false;

					foreach ($q2 as $cfk => $cfv) {

						if ($cfk == 'custom_field_name') {

							$the_name = $cfv;
						}

						if ($cfk == 'custom_field_value') {

							$the_val = $cfv;
						}

						$i++;
					}

					if ($the_name != false and $the_val != false) {
						if ($return_full == false) {

							$the_data_with_custom_field__stuff[$the_name] = $the_val;
						} else {

							$the_data_with_custom_field__stuff[$the_name] = $q2;
						}
					}
				}
			}
		}
	}

	$result = $the_data_with_custom_field__stuff;
	//$result = (array_change_key_case($result, CASE_LOWER));
	$result = remove_slashes_from_array($result);
	$result = replace_site_vars_back($result);
	//d($result);
	return $result;
}

/*document_ready('test_document_ready_api');

 function test_document_ready_api($layout) {

 //   $layout = modify_html($layout, $selector = '.editor_wrapper', 'append', 'ivan');
 //$layout = modify_html2($layout, $selector = '<div class="editor_wrapper">', '');
 return $layout;
 }*/

/**
 * make_custom_field
 *
 * @desc make_custom_field
 * @access      public
 * @category    forms
 * @author      Microweber
 * @link        http://microweber.com
 * @param string $field_type
 * @param string $field_id
 * @param array $settings
 */
api_expose('make_custom_field');
function custom_field_names_for_table($table) {
	$table = db_escape_string($table);
	$table1 = db_get_assoc_table_name($table);

	$table = MW_DB_TABLE_CUSTOM_FIELDS;
	$q = false;
	$results = false;

	$q = "SELECT *, count(id) as qty FROM $table where   custom_field_type IS NOT NULL and to_table='{$table1}' and custom_field_name!='' group by custom_field_name, custom_field_type order by qty desc limit 100";
	//d($q);
	$crc = (crc32($q));

	$cache_id = __FUNCTION__ . '_' . $crc;

	$results = db_query($q, $cache_id, 'custom_fields/global');

	if (isarr($results)) {
		return $results;
	}

}

function make_custom_field($field_id = 0, $field_type = 'text', $settings = false) {
	$data = false;
	$form_data = array();
	if (is_array($field_id)) {

		if (!empty($field_id)) {
			$data = $field_id;
			return make_field($field_id, false, 'y');
		}
	} else {
		if ($field_id != 0) {

			return make_field($field_id);

			//
			// error('no permission to get data');
			//  $form_data = db_get_id('table_custom_fields', $id = $field_id, $is_this_field = false);
		}
	}
	//return make_field($field_id);
	/*
	 if (isset($data) and is_array($data)) {
	 if (!empty($data)) {
	 if (isset($data['custom_field_type'])) {
	 $field_type = $data['custom_field_type'];
	 }
	 if (isset($data['type'])) {
	 $field_type = $data['type'];
	 }
	 }
	 }

	 if (isset($data['field_id'])) {
	 $copy_from = $data['field_id'];
	 if (is_admin() == true) {

	 $table_custom_field = MW_TABLE_PREFIX . 'custom_fields';
	 $form_data = db_get_id($table_custom_field, $id = $copy_from, $is_this_field = false);
	 if (isset($form_data['custom_field_type'])) {
	 $field_type = $data['type'] = $form_data['custom_field_type'];
	 }
	 //d($field_type);
	 }
	 // d($form_data);
	 }

	 if (isset($data['copy_from'])) {
	 $copy_from = $data['copy_from'];
	 if (is_admin() == true) {

	 $table_custom_field = MW_TABLE_PREFIX . 'custom_fields';
	 $form_data = db_get_id($table_custom_field, $id = $copy_from, $is_this_field = false);
	 $field_type = $form_data['custom_field_type'];
	 $form_data['id'] = 0;
	 }
	 //d($form_data);
	 }
	 $settings = false;
	 if (isset($data['settings'])) {
	 $settings = $data['settings'];
	 }
	 $dir = INCLUDES_DIR;
	 $dir = $dir . DS . 'custom_fields' . DS;
	 $field_type = str_replace('..', '', $field_type);
	 // d($field_type);
	 if ($settings == true) {
	 $file = $dir . $field_type . '_settings.php';
	 } else {
	 $file = $dir . $field_type . '.php';
	 }

	 return make_field($form_data, false, $settings);

	 define_constants();
	 $l = new MwView($file);

	 $l -> params = $data;
	 $l -> data = $form_data;
	 // var_dump($l);
	 //$l->set($l);

	 $l = $l -> __toString();
	 // var_dump($l);
	 $l = parse_micrwober_tags($l, $options = array('parse_only_vars' => 1));

	 return $l;*/

}

api_expose('save_custom_field');

function save_custom_field($data) {
	$id = user_id();
	if ($id == 0) {
		error('Error: not logged in.');
	}
	$id = is_admin();
	if ($id == false) {
		error('Error: not logged in as admin.');
	}
	$data_to_save = ($data);

	$table_custom_field = MW_TABLE_PREFIX . 'custom_fields';

	if (isset($data_to_save['for'])) {
		$data_to_save['to_table'] = guess_table_name($data_to_save['for']);
	}
	if (isset($data_to_save['cf_id'])) {
		$data_to_save['id'] = intval($data_to_save['cf_id']);

		$table_custom_field = MW_TABLE_PREFIX . 'custom_fields';
		$form_data_from_id = db_get_id($table_custom_field, $data_to_save['id'], $is_this_field = false);
		if (isset($form_data_from_id['id'])) {
			if (!isset($data_to_save['to_table'])) {
				$data_to_save['to_table'] = $form_data_from_id['to_table'];
			}
			if (!isset($data_to_save['to_table_id'])) {
				$data_to_save['to_table_id'] = $form_data_from_id['to_table_id'];
			}
		}

		if (isset($data_to_save['copy_to_table_id'])) {

			$cp = db_copy_by_id($table_custom_field, $data_to_save['cf_id']);
			$data_to_save['id'] = $cp;
			$data_to_save['to_table_id'] = $data_to_save['copy_to_table_id'];
			//$data_to_save['id'] = intval($data_to_save['cf_id']);
		}

	}

	if (!isset($data_to_save['to_table'])) {
		$data_to_save['to_table'] = 'table_content';
	}
	$data_to_save['to_table'] = db_get_assoc_table_name($data_to_save['to_table']);
	if (!isset($data_to_save['to_table_id'])) {
		$data_to_save['to_table_id'] = '0';
	}
	if (isset($data['options'])) {
		$data_to_save['options'] = encode_var($data['options']);
	}

	//  $data_to_save['debug'] = 1;

	$save = save_data($table_custom_field, $data_to_save);

	cache_clean_group('custom_fields');
	//	$save = make_field($save);
	return $save;

	//exit
}

api_expose('reorder_custom_fields');

function reorder_custom_fields($data) {

	$adm = is_admin();
	if ($adm == false) {
		error('Error: not logged in as admin.');
	}

	$table = MW_TABLE_PREFIX . 'custom_fields';

	foreach ($data as $value) {
		if (is_arr($value)) {
			$indx = array();
			$i = 0;
			foreach ($value as $value2) {
				$indx[$i] = $value2;
				$i++;
			}

			db_update_position($table, $indx);
			return true;
			// d($indx);
		}
	}
}

api_expose('remove_field');

function remove_field($id) {
	$uid = user_id();
	if ($uid == 0) {
		error('Error: not logged in.');
	}
	$uid = is_admin();
	if ($uid == false) {
		exit('Error: not logged in as admin.');
	}
	if (is_array($id)) {
		extract($id);
	} else {

	}

	$id = intval($id);
	if (isset($cf_id)) {
		$id = intval($cf_id);
	}

	if ($id == 0) {

		return false;
	}

	$custom_field_table = MW_TABLE_PREFIX . 'custom_fields';
	$q = "DELETE FROM $custom_field_table where id='$id'";

	db_q($q);

	cache_clean_group('custom_fields');

	return true;
}

/**
 * make_field
 *
 * @desc make_field
 * @access      public
 * @category    forms
 * @author      Microweber
 * @link        http://microweber.com
 * @param string $field_type
 * @param string $field_id
 * @param array $settings
 */
function make_field($field_id = 0, $field_type = 'text', $settings = false) {

	if (is_array($field_id)) {
		if (!empty($field_id)) {
			$data = $field_id;
		}
	} else {
		if ($field_id != 0) {
			$data = db_get_id('table_custom_fields', $id = $field_id, $is_this_field = false);
		}
	}
	if (isset($data['settings']) or (isset($_REQUEST['settings']) and trim($_REQUEST['settings']) == 'y')) {

		$settings == true;
	}

	if (isset($data['copy_from'])) {
		$copy_from = intval($data['copy_from']);
		if (is_admin() == true) {

			$table_custom_field = MW_TABLE_PREFIX . 'custom_fields';
			$form_data = db_get_id($table_custom_field, $id = $copy_from, $is_this_field = false);
			if (is_arr($form_data)) {

				$field_type = $form_data['custom_field_type'];
				$data['id'] = 0;
				if (isset($data['save_on_copy'])) {

					$cp = $form_data;
					$cp['id'] = 0;
					$cp['copy_of_field'] = $copy_from;
					if (isset($data['to_table'])) {
						$cp['to_table'] = ($data['to_table']);
					}
					if (isset($data['to_table_id'])) {
						$cp['to_table_id'] = ($data['to_table_id']);
					}
					save_custom_field($cp);
					$data = $cp;
				} else {
					$data = $form_data;
				}

			}

		}
		//d($form_data);
	} else if (isset($data['field_id'])) {

		$data = db_get_id('table_custom_fields', $id = $data['field_id'], $is_this_field = false);
	}

	if (isset($data['custom_field_type'])) {
		$field_type = $data['custom_field_type'];
	}

	if (!isset($data['custom_field_required'])) {
		$data['custom_field_required'] = 'n';
	}

	if (isset($data['type'])) {
		$field_type = $data['type'];
	}

	if (isset($data['field_type'])) {
		$field_type = $data['field_type'];
	}

	if (isset($data['field_values']) and !isset($data['custom_field_value'])) {
		$data['custom_field_values'] = $data['field_values'];
	}

	$data['custom_field_type'] = $field_type;

	if (isset($data['custom_field_value']) and strtolower($data['custom_field_value']) == 'array') {
		if (isset($data['custom_field_values']) and is_string($data['custom_field_values'])) {

			$try = base64_decode($data['custom_field_values']);

			if ($try != false and strlen($try) > 5) {
				$data['custom_field_values'] = unserialize($try);
			}
		}
	}
	if (isset($data['options']) and $data['options'] != '') {
		$data['options'] = decode_var($data['options']);
		//	d($data['options'] );
	}

	$dir = INCLUDES_DIR;
	$dir = $dir . DS . 'custom_fields' . DS;
	$field_type = str_replace('..', '', $field_type);
	if ($settings == true or isset($data['settings'])) {
		$file = $dir . $field_type . '_settings.php';
	} else {
		$file = $dir . $field_type . '.php';
	}
	if (is_file($file)) {
		$l = new MwView($file);
		//
		$l -> settings = $settings;

		if (isset($data) and !empty($data)) {
			$l -> data = $data;
		} else {
			$l -> data = array();
		}

		$layout = $l -> __toString();

		return $layout;
	}
}