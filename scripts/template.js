function showTemplateList(records, panel, conf) {
	if(records.length == 0) {
		panel.innerHTML = "没有虚拟机模板!";
	}else {
		var headers = [
			{
				title: "#",
				value: function(c, r) {
					c.innerHTML = r.id;
				}
			},
			{
				title: "名称",
				value: function(c, r) {
					c.innerHTML = r.vc_name;
				}
			},
			{
				title: "操作系统",
				value: function(c, r) {
					c.innerHTML = r.vc_os + " " + r.vc_version + " " + r.tiu_bits + "bits";
				}
			},
			{
				title: "大小（GB)",
				value: function(c, r) {
					c.innerHTML = r.siu_size;
				}
			}
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
						editTemplateRecord(obj.conf, config_div, obj.rec, 'EDIT_VMTEMPLATE', '修改');
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
							onOK: function() { updatePanelContentRPC(obj.conf.name, 'DEL_VMTEMPLATE', {_id: obj.rec.id}); return true;},
							onCancel: function() {return true;}
						});
					});
				}
			};
		}
		newDBGrid(records, headers, "100%", panel);
	}
}

function addTemplateRecordValidate(form) {
	with(form) {
		if(!checkDOMUser(_vc_name, '名称不能为空且只包含字母、数字和下划线！', true)) {
			return false;
		}
		if(!checkDOMUser(_vc_version, '版本不能为空且只包含字母、数字和下划线！', true)) {
			return false;
		}
	}
	return true;
}

function editTemplateRecord(conf, panel, rec, action, btn_txt) {
	var form = newDefaultRPCForm(panel, action, conf.name, addTemplateRecordValidate);
	if (action == 'SAVE_VMGUEST_DISK_TEMPLATE') {
		form.appendChild(newInputElement('hidden', '_disk_id', rec.disk_id));
	}else {
		form.appendChild(newInputElement('hidden', '_id', rec.id));
	}

	var tbl = newTableElement('', 0, 0, 2, '', 6, 2, ['right', 'left'], 'middle', form);

	tblCell(tbl, 0, 0).innerHTML = '模板名称：';
	tblCell(tbl, 0, 1).appendChild(newInputElement('text', '_vc_name', rec.vc_name));

	tblCell(tbl, 1, 0).innerHTML = '操作系统：';
	var os_opts = ['Linux', 'Windows', 'FreeBSD'];
	var os_sel = newSelector(os_opts, rec.vc_os, '_vc_os');
	tblCell(tbl, 1, 1).appendChild(os_sel);

	tblCell(tbl, 2, 0).innerHTML = '发行版本：';
	tblCell(tbl, 2, 1).appendChild(newInputElement('text', '_vc_version', rec.vc_version));

	tblCell(tbl, 3, 0).innerHTML = 'CPU体系：';
	var bits_opts = [{'txt': 'x86 32位', 'val': '32'}, {'txt': 'x86 64位', 'val': '64'}];
	var bits_sel = newSelector(bits_opts, rec.tiu_bits, '_tiu_bits');
	tblCell(tbl, 3, 1).appendChild(bits_sel);

	tblCell(tbl, 4, 0).innerHTML = '语言：';
	var lang_opts = ['en_us', 'zh_cn'];
	var lang_sel = newSelector(lang_opts, rec.vc_lang, '_vc_lang');
	tblCell(tbl, 4, 1).appendChild(lang_sel);

	tblCell(tbl, 5, 1).appendChild(newInputElement('submit', '', btn_txt));
	tblCell(tbl, 5, 1).appendChild(newCancelConfigButton(conf));
}
