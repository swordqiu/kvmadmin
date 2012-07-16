function showHostList(records, panel, conf) {
	if(records.length == 0) {
		panel.innerHTML = "没有配置物理主机!";
	}else {
		var headers = [
			{
				title: "#",
				value: function(c, r) {
					c.innerHTML = r.id;
				}
			},
			{
				title: "主机",
				value: function(c, r) {
					c.innerHTML = r.vc_name + "(" + r.vc_address + ")";
				}
			},
			{
				title: "内存(MB)",
				value: function(c, r) {
					c.innerHTML = r.siu_memory + "(使用:" + Number(r.used_mem) + ")";
				}
			},
			{
				title: "虚拟机数",
				value: function(c, r) {
					c.innerHTML = Number(r.vm_count) + "(运行:" + Number(r.live_vm_count) + ")";
				}
			},
			{
				title: "存储空间(GB)",
				value: function(c, r) {
					c.innerHTML = r.siu_disk_size + "(使用:" + Number(r.vm_disk_sz) + ")";
				}
			},
			{
				title: "虚拟机目录",
				value: function(c, r) {
					c.innerHTML = r.vc_vm_dir;
				}
			}
			/*{
				title: "网桥",
				value: function(c, r) {
					c.innerHTML = r.vc_bridge_name;
				}
			}*/
		];
		if(_session_user === 'admin') {
			headers[headers.length] = {
				title: "",
				value: function(c, r) {
					var edit_btn = newInputElement('button', '', '修改');
					c.appendChild(edit_btn);
					edit_btn.rec = r;
					edit_btn.conf = conf;
					edit_btn.__destruct = function() {
						this.rec = null;
						this.conf = null;
					};
					EventManager.Add(edit_btn, 'click', function(ev, obj) {
						var config_div = getContentPanelConfigArea(obj.conf);
						editVMHostRecord(obj.conf, config_div, obj.rec);
					});
					var edit_net_btn = newInputElement('button', '', '配置网络');
					c.appendChild(edit_net_btn);
					edit_net_btn.rec = r;
					edit_net_btn.conf = conf;
					edit_net_btn.__destruct = function() {
						this.rec = null;
						this.conf = null;
					};
					EventManager.Add(edit_net_btn, 'click', function(ev, obj) {
						var frame = getVMHostEditFrame(obj.rec.id, obj.rec.vc_name);
						var tbl = newTableElement('100%', 0, 0, 0, '', 1, 1, 'center', 'top', frame);
						var div = tblCell(tbl, 0, 0);
						div.id = '_vm_host_net_detailed_config';
						div.style.padding = '8px';
						makeContentPanel(div, {
							frame_scheme: 0,
							expand: 'none',
							title: '主机' + obj.rec.vc_name + '网络设置',
							content_func: showVMHostNetworks,
							menus: [{'txt':'新增', 'url': 'images/addnewitem.png'}],
							config_func: addVMHostNetwork,
							query_vars: "a.id, a.vc_bridge, a.vc_eth, b.vc_name, b.vc_addr_start, b.tiu_netmask",
							query_tables: "vm_host_net_tbl a left join vm_network_tbl b on a.net_id=b.id",
							query_conditions: "a.host_id=" + obj.rec.id,
							query_order: "a.dt_created asc"
						});
					});
					if(Number(r.vm_count) == 0) {
						var del_btn = newInputElement('button', '', '删除');
						c.appendChild(del_btn);
						del_btn.rec = r;
						del_btn.conf = conf;
						del_btn.__destruct = function() {
							this.rec = null;
							this.conf = null;
						};
						EventManager.Add(del_btn, 'click', function(ev, obj) {
							showConfirm({
								msg: '是否确定删除?',
								onOK: function() { updatePanelContentRPC(obj.conf.name, 'DEL_VMHOST', {_id: obj.rec.id}); return true;},
								onCancel: function() {return true;}
							});
						});
					}
				}
			};
		}
		newDBGrid(records, headers, "100%", panel);
	}
}

function addVMHostRecordValidate(form) {
	with(form) {
		if(!checkDOMNonempty(_vc_name, '主机名不能为空！')) {
			return false;
		}
		if(!checkDOMIP(_vc_address, '主机地址不能为空！', true)) {
			return false;
		}
		if(!checkDOMDecimal(_siu_disk_size, 5, '存储容量必须为数字！', true)) {
			return false;
		}
	}
	return true;
}

function addVMHostRecord(conf, menuText, panel) {
	editVMHostRecord(conf, panel);
}

function editVMHostRecord(conf, panel, rec) {
	if('undefined' === typeof rec) {
		var action = 'ADD_VMHOST';
		var btn_txt = '添加';
		rec = {vc_name: '', vc_address: '', siu_memory: 2048, vc_bridge_name: 'br0', id: 0, vc_vm_dir: '/home/vm/vms/', vc_template_dir: '/home/vm/templates/', vc_iso_dir: '/home/vm/iso/'};
	}else {
		var action = 'EDIT_VMHOST';
		var btn_txt = '修改';
	}

	var form = newDefaultRPCForm(panel, action, conf.name, addVMHostRecordValidate);
	form.appendChild(newInputElement('hidden', '_id', rec.id));

	var tbl = newTableElement('', 0, 0, 2, '', 9, 2, ['right', 'left'], 'middle', form);

	tblCell(tbl, 0, 0).innerHTML = '主机名：';
	tblCell(tbl, 0, 1).appendChild(newInputElement('text', '_vc_name', rec.vc_name));
	tblCell(tbl, 1, 0).innerHTML = 'IP地址：';
	tblCell(tbl, 1, 1).appendChild(newInputElement('text', '_vc_address', rec.vc_address));
	tblCell(tbl, 2, 0).innerHTML = '内存容量(MB)：';
	tblCell(tbl, 2, 1).appendChild(newSelector(makeNumberRange(512, 32768, 512), rec.siu_memory, '_siu_memory'));
	tblCell(tbl, 3, 0).innerHTML = '网桥名称：';
	tblCell(tbl, 3, 1).appendChild(newInputElement('text', '_vc_bridge_name', rec.vc_bridge_name));
	tblCell(tbl, 4, 0).innerHTML = '虚拟机路径：';
	tblCell(tbl, 4, 1).appendChild(newInputElement('text', '_vc_vm_dir', rec.vc_vm_dir));
	tblCell(tbl, 5, 0).innerHTML = '存储容量(GB)：';
	tblCell(tbl, 5, 1).appendChild(newInputElement('text', '_siu_disk_size', rec.siu_disk_size));
	tblCell(tbl, 6, 0).innerHTML = '模板目录(共享)：';
	tblCell(tbl, 6, 1).appendChild(newInputElement('text', '_vc_template_dir', rec.vc_template_dir));
	tblCell(tbl, 7, 0).innerHTML = 'ISO目录(共享)：';
	tblCell(tbl, 7, 1).appendChild(newInputElement('text', '_vc_iso_dir', rec.vc_iso_dir));

	tblCell(tbl, 8, 1).appendChild(newInputElement('submit', '', btn_txt));
	tblCell(tbl, 8, 1).appendChild(newCancelConfigButton(conf));
}

var __current_vm_host_id = null;
var __current_vm_host_name = null;
function getVMHostEditFrame(host_id, host_name) {
	__current_vm_host_id   = host_id;
	__current_vm_host_name = host_name;
	var frame = document.getElementById('_vm_host_detailed_edit_pane');
	frame.align = 'center';
	removeAllChildNodes(frame);
	var cancel_btn = newInputElement('button', '', '取消');
	frame.appendChild(cancel_btn);
	EventManager.Add(cancel_btn, 'click', function(ev, obj) {
		var frame = document.getElementById('_vm_host_detailed_edit_pane');
		removeAllChildNodes(frame);
		//redrawPanelContent('_host_list', true, false);
	});
	return frame;
}

function showVMHostNetworks(records, panel, conf) {
	if (records.length == 0) {
		panel.innerHTML = "还没有配置主机网络";
	}else {
		var headers = [
		{
			title: '#',
			value: function(c, r) {
				c.innerHTML = r.id;
			}
		},
		{
			title: '网桥',
			value: function(c, r) {
				c.innerHTML = r.vc_bridge;
				if (r.vc_eth.length > 0) {
					c.innerHTML += '(' + r.vc_eth + ')';
				}
			}
		},
		{
			title: '网络',
			value: function(c, r) {
				c.innerHTML = r.vc_name + '(' + r.vc_addr_start + '/' + r.tiu_netmask + ')';
			}
		},
		{
			title: '',
			value: function(c, r) {
				var edit_btn = newInputElement('button', '', '修改');
				c.appendChild(edit_btn);
				edit_btn.rec = r;
				edit_btn.conf = conf;
				edit_btn.__destruct = function() {
					this.rec = null;
					this.conf = null;
				};
				EventManager.Add(edit_btn, 'click', function(ev, obj) {
					var config_div = getContentPanelConfigArea(obj.conf);
					editVMHostNetwork(obj.conf, config_div, obj.rec);
				});

				var del_btn = newInputElement('button', '', '删除');
				c.appendChild(del_btn);
				del_btn.rec = r;
				del_btn.conf = conf;
				del_btn.__destruct = function() {
					this.rec = null;
					this.conf = null;
				};
				EventManager.Add(del_btn, 'click', function(ev, obj) {
					showConfirm({
						msg: '是否确定删除?',
						onOK: function() { updatePanelContentRPC(obj.conf.name, 'DEL_VMHOST_NETWORK', {_id: obj.rec.id}); return true;},
						onCancel: function() {return true;}
					});
				});
			}
		}
		];
		newDBGrid(records, headers, '100%', panel);
	}
}

function addVMHostNetwork(conf, menuText, panel) {
	editVMHostNetwork(conf, panel);
}

function editVMHostNetworkValidate(form) {
	with(form) {
		if (!checkDOMNonempty(_vc_bridge, '请输入网桥名称')) {
			return false;
		}
	}
	return true;
}

function editVMHostNetwork(conf, panel, rec) {
	if(_vm_net_records == null || _vm_net_records.length == 0) {
		panel.appendChild(newParagraph('没有虚拟网络！'));
		panel.appendChild(newCancelConfigButton(conf));
		return;
	}
	if (typeof(rec) === 'undefined') {
		rec = {net_id: 0, vc_bridge: '', vc_eth: ''};
		var action = 'ADD_VMHOST_NETWORK';
		var btn_txt = '添加';
	}else {
		var action = 'EDIT_VMHOST_NETWORK';
		var btn_txt = '修改';
	}
	var form = newDefaultRPCForm(panel, action, conf.name, editVMHostNetworkValidate);
	if (action == 'EDIT_VMHOST_NETWORK') {
		form.appendChild(newInputElement('hidden', '_id', rec.id));
	}
        form.appendChild(newInputElement('hidden', '_host_id', __current_vm_host_id));
        var tbl = newTableElement('', 0, 0, 2, '', 4, 2, ['right', 'left'], 'top', form);
	tblCell(tbl, 0, 0).innerHTML = '网络：';
	var net_options = Array();
	for(var i = 0; i < _vm_net_records.length; i ++) {
		net_options[i] = {txt: _vm_net_records[i].vc_name, val: _vm_net_records[i].id};
	}
	var net_sel = newSelector(net_options, rec.net_id, '_net_id');
	tblCell(tbl, 0, 1).appendChild(net_sel);
	tblCell(tbl, 1, 0).innerHTML = '网桥：';
	tblCell(tbl, 1, 1).appendChild(newInputElement('text', '_vc_bridge', rec.vc_bridge));
	tblCell(tbl, 2, 0).innerHTML = '网络接口：';
	tblCell(tbl, 2, 1).appendChild(newInputElement('text', '_vc_eth', rec.vc_eth));
	tblCell(tbl, 3, 1).appendChild(newInputElement('submit', '', '保存'));
	tblCell(tbl, 3, 1).appendChild(newCancelConfigButton(conf))
}

