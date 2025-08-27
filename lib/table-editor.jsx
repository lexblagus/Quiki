// -----------------------------------------------------------------------------
window.save = () => {
	const xhr = new XMLHttpRequest();
	xhr.open('POST', '?save', true);
	xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
	xhr.onreadystatechange = function () {
		if (xhr.readyState === 4 && xhr.status === 200) {
			location.reload();
		}
	};
	document.getElementById('root').innerHTML = 'loading…';
	const main = document.getElementsByTagName('main')[0];
	const dataToSend = `sourcecode=${encodeURIComponent(main.innerHTML.trim())}`;
	xhr.send(dataToSend);
}
// -----------------------------------------------------------------------------
const li = document.createElement('li');
li.innerHTML = '<a href="#" onclick="javascript:(function(){ event.preventDefault(); save(); })(event)">save</a>';
const ul = document.querySelector('body > header > nav > ul');
ul.appendChild(li);
// =============================================================================
const uuidv4 = () => ([1e7] + -1e3 + -4e3 + -8e3 + -1e11)
	.replace(/[018]/g, c =>
		(c ^ crypto.getRandomValues(new Uint8Array(1))[0] & 15 >> c / 4).toString(16)
	);
// -----------------------------------------------------------------------------
const generatePassword = () => {
	var letters = 'abcdefghijklmnopqrstuvwxyz';
	const numbers = '1234567890';
	const specialChars = '!@#$%*^-_=+';
	var pattern = letters.toUpperCase() + letters.toLowerCase() + numbers + specialChars;
	var passLength = 16;
	var randomPass = Array(passLength).join().split(',').map(
		(o, i, p) => {
			return pattern.split('')[Math.floor(Math.random() * pattern.length)];
		}
	).join('');
	return randomPass;
}
// =============================================================================
const TableEditor = () => {
	const [data, setData] = React.useState([]);
	const [initialData, setInitialData] = React.useState([]);
	const [dirty, setDirty] = React.useState([]);
	const cellRefs = React.useRef({});
	const columnRefs = React.useRef({});
	// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
	React.useEffect(() => {
		const dataEl = document.getElementById('data');
		if (dataEl) {
			const jsonText = dataEl.innerText;
			try {
				const parsedData = JSON.parse(jsonText);
				parsedData.forEach((sheet, sheetIndex) => {
					if (!parsedData[sheetIndex].meta) {
						parsedData[sheetIndex].meta = {};
					}
					if (!parsedData[sheetIndex].meta.id) {
						parsedData[sheetIndex].meta.id = uuidv4();
					}
					if (!parsedData[sheetIndex].columns) {
						parsedData[sheetIndex].columns = [];
					}
					if (!parsedData[sheetIndex].body) {
						parsedData[sheetIndex].body = [];
					}
					if (sheet.columns.length > 0 && sheet.body.length === 0) {
						// At least one empty row at begin
						sheet.body.push({});
					}
					sheet.body.forEach((row, rowIndex) => {
						if (!row.id) {
							row.id = uuidv4();
						}
						if (!row.cells) {
							row.cells = {};
						}
						sheet.columns.forEach((column, columnIndex) => {
							if (!column.id) {
								column.id = uuidv4();
							}
							if (!column.type) {
								column.type = 'text';
							}
							if (!column.filter) {
								column.filter = { enabled: false, value: '' };
							}
							if (!row.cells[column.id]) {
								row.cells[column.id] = {};
							}
							if (!row.cells[column.id].id) {
								row.cells[column.id].id = uuidv4();
							}
							if (!row.cells[column.id].value) {
								row.cells[column.id].value = '';
							}
						});
					})
				});
				setData(parsedData);
				setInitialData(structuredClone(parsedData));
			} catch (e) {
				console.error("Failed to parse JSON from <script id='data'>:", e);
			}
		}
	}, []); // run only once on mount
	// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
	React.useEffect(() => {
		data && data.forEach((sheet, sheetIndex) => {
			// At least one empty row when all are deleted
			if (sheet.columns.length > 0 && sheet.body.length === 0) {
				handleAddRow(sheetIndex, 0);
			}

			// set content editable values by ref (headers)
			sheet.columns.forEach((column, columnIndex) => {
				const headerFilterValue = column.filter.value;
				const el = columnRefs.current[`${sheetIndex} ${column.id}`];
				if (el && el.innerText !== headerFilterValue) {
					el.innerText = headerFilterValue;
				}
			});

			// set content editable values by ref (cells)
			sheet.body.forEach((row, rowIndex) => {
				sheet.columns.forEach((column, columnIndex) => {
					const cell = row.cells[column.id];
					const el = cellRefs.current[cell.id];
					if (el && el.innerHTML !== cell.value) {
						if (cell.type === 'html' || column.type === 'html') {
							el.innerHTML = cell.value;
						} else {
							el.innerText = cell.value;
						}
					}
				});
			});
		});

		data && setDirty(
			data.map(
				(sheet, sheetIndex) =>
					JSON.stringify(data[sheetIndex]) !== JSON.stringify(initialData[sheetIndex])
			)
		);

		// update JSON data tag (to be saved)
		document.getElementById('data').innerHTML = JSON.stringify(data, null, '\t');
	}, [data]); // run when data is changed
	// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
	const filterRow = (sheetIndex, row, rowIndex) => {
		const filters = data[sheetIndex].columns.filter(
			column =>
				column.filter && 
				column.filter.enabled && 
				column.filter.value !== ''
		);

		if (filters.length === 0) { return true; }

		const columnFilters = filters.reduce(
			(acc, item) => {
				acc[item.id] = item.filter.value;
				return acc;
			}, {});

		const columns = data[sheetIndex].columns;

		return columns.some(column => {
			const cell = row.cells[column.id];
			const filterValue = (columnFilters[column.id] || '').toLowerCase();
			const keyed = column.id in columnFilters;

			if(cell.type === 'function'){
				return false
			}

			if(cell.type === 'html'){
				const parser = new DOMParser();
				const doc = parser.parseFromString(cell.value, "text/html");
				const text = doc.body.innerText.toLowerCase();
				const htmlMatches = text.search(filterValue) > -1;
				return keyed && htmlMatches;
			}

			const matches = cell.value.toLowerCase().search(filterValue) > -1;
			return keyed && matches;
		})
	};
	// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
	const handleToggleFilter = (sheetIndex, columnId) => {
		const newData = structuredClone(data);
		const columnIndex = newData[sheetIndex].columns.findIndex(item => item.id === columnId);
		const column = newData[sheetIndex].columns[columnIndex];
		column.filter = {
			enabled: !column.filter.enabled,
			value: column.filter.value,
		};
		setData(newData);
	};
	// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
	const handleChangeFilter = (sheetIndex, columnId) => {
		const newData = structuredClone(data);
		const columnIndex = newData[sheetIndex].columns.findIndex(item => item.id === columnId);
		const column = newData[sheetIndex].columns[columnIndex];
		const ref = columnRefs.current[`${sheetIndex} ${column.id}`];
		const newText = ref ? ref.innerText : '';
		column.filter.value = newText;
		setData(newData);
	};
	// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
	const handleGeneratePassword = (sheetIndex, rowIndex, columnId) => {
		const cellId = data[sheetIndex].body[rowIndex].cells[columnId].id;
		const newText = generatePassword();
		const newData = [...data];
		const sheet = newData[sheetIndex];
		newData[sheetIndex].body[rowIndex].cells[columnId].value = newText;
		setData(newData);
	};
	// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
	const handleChangeCell = (sheetIndex, rowIndex, columnId) => {
		const cellId = data[sheetIndex].body[rowIndex].cells[columnId].id;
		const cellType = data[sheetIndex].body[rowIndex].cells[columnId].type || data[sheetIndex].columns.find(column => column.id === columnId).type;
		const newText =
			cellRefs.current[cellId] &&
			(cellType === 'html' ? cellRefs.current[cellId].innerHTML : cellRefs.current[cellId].innerText) ||
			'';
		const newData = [...data];
		const sheet = newData[sheetIndex];
		newData[sheetIndex].body[rowIndex].cells[columnId].value = newText;
		setData(newData);
	};
	// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
	const handleAddRow = (sheetIndex, rowIndex) => {
		const newData = [...data];
		const sheet = newData[sheetIndex];

		const emptyRow = {};
		emptyRow.id = uuidv4();
		emptyRow.cells = {};
		sheet.columns.forEach(col => {
			emptyRow.cells[col.id] = { id: uuidv4(), value: '', filter: { enabled: false, value: '' } };
		});

		newData[sheetIndex] = {
			...sheet,
			body: [
				...sheet.body.slice(0, rowIndex),
				emptyRow,
				...sheet.body.slice(rowIndex),
			],
		};
		setData(newData);
	};
	// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
	const handleMoveRow = (sheetIndex, rowIndex, toIndex) => {
		if (toIndex < 0 || toIndex >= data[sheetIndex].body.length) return;

		const newData = [...data];
		const sheet = newData[sheetIndex];
		const body = [...sheet.body];

		const temp = body[toIndex];
		body[toIndex] = body[rowIndex];
		body[rowIndex] = temp;

		newData[sheetIndex] = {
			...sheet,
			body,
		};
		setData(newData);
	};
	// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
	const handleRemoveRow = (sheetIndex, rowIndex) => {
		let message = 'Are you sure about removing this row?' + '\n\n';

		data[sheetIndex].columns.forEach(column => {
			message +=
				(column.title ? `${column.title}: ` : '') +
				(data[sheetIndex].body[rowIndex].cells[column.id].value || '') +
				'\n';
		});

		if (window.confirm(message)) {
			const newData = [...data];
			newData[sheetIndex] = {
				...newData[sheetIndex],
				body: [
					...newData[sheetIndex].body.slice(0, rowIndex),
					...newData[sheetIndex].body.slice(rowIndex + 1),
				],
			};
			setData(newData);
		}
	};
	// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
	const handleSave = () => {
		const xhr = new XMLHttpRequest();
		xhr.open('POST', '?save', true);
		xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
		xhr.onreadystatechange = function () {
			if (xhr.readyState === 4 && xhr.status === 200) {
				location.reload();
			}
		};
		document.getElementById('root').innerHTML = 'loading…';
		const main = document.getElementsByTagName('main')[0];
		const dataToSend = `sourcecode=${encodeURIComponent(main.innerHTML.trim())}`;
		xhr.send(dataToSend);
	};
	// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
	return (
		<React.Fragment>
			{data.map((sheet, sheetIndex) => <React.Fragment key={sheet.meta.id}>
				{sheet.meta.title && (() => {
					switch (sheet.meta.level) {
						case 1: return <h1>{sheet.meta.title}</h1>;
						case 2: return <h2>{sheet.meta.title}</h2>;
						case 4: return <h4>{sheet.meta.title}</h4>;
						case 5: return <h5>{sheet.meta.title}</h5>;
						case 6: return <h6>{sheet.meta.title}</h6>;
						default: return <h3>{sheet.meta.title}</h3>;
					}
				})()}
				<table>
					<thead>
						<tr>
							{sheet.columns.map(column =>
								<th
									key={column.id}
									data-id={column.id}
									className="cell-padding"
								>
									<div className="cells-and-actions">
										<div style={{
											textAlign: column.align,
											...column.style,
										}}>
											{column.title}
										</div>
										<div className="header-actions">
											{column.type !== 'function' && (
												<button
													title="filter by contents"
													className="header-action"
													onClick={() => handleToggleFilter(sheetIndex, column.id)}
												><i className="fa-solid fa-filter"></i></button>
											)}
										</div>
									</div>
									{column.filter.enabled && column.type !== 'function' && (
										<div
											contentEditable suppressContentEditableWarning
											ref={el => columnRefs.current[`${sheetIndex} ${column.id}`] = el}
											onBlur={() => handleChangeFilter(sheetIndex, column.id)}
											onKeyUp={() => handleChangeFilter(sheetIndex, column.id)}
											className="cell-padding filter-text"
											style={{
												textAlign: column.align,
											}}
										></div>
									)}
								</th>
							)}
							{sheet.columns.length > 0 && (
								<th style={{ textAlign: 'center' }}>Actions</th>
							)}
						</tr>
					</thead>
					<tbody>
						{sheet.body
							.filter((row, rowIndex) => filterRow(sheetIndex, row, rowIndex))
							.map((row, rowIndex) =>
							<tr key={row.id} data-id={row.id}>
								{sheet.columns.map(column =>
									<React.Fragment key={column.id}>
										{(() => {
											switch (row.cells[column.id].type || column.type) {
												case 'header': return <th style={{...column.style, ...row.cells[column.id].style}}>
													<div
														contentEditable suppressContentEditableWarning
														ref={el => (cellRefs.current[row.cells[column.id].id] = el)}
														onBlur={() => handleChangeCell(sheetIndex, rowIndex, column.id)}
														className="cell-padding"
														style={{
															textAlign: row.cells[column.id].align || column.align,
														}}
													></div>
												</th>;
												case 'password': return <td style={{...column.style, ...row.cells[column.id].style}}>
													<div className="cells-and-actions">
														<div
															contentEditable suppressContentEditableWarning
															ref={el => (cellRefs.current[row.cells[column.id].id] = el)}
															onBlur={() => handleChangeCell(sheetIndex, rowIndex, column.id)}
															className="cell-padding"
															style={{
																textAlign: row.cells[column.id].align || column.align,
															}}
														></div>
														<div>
															<button
																title="generate new password"
																className="action"
																onClick={() => handleGeneratePassword(sheetIndex, rowIndex, column.id)}
															><i className="fas fa-key"></i></button>
														</div>
													</div>
												</td>;
												case 'uri': return <td style={{ textAlign: column.align, ...column.style, ...row.cells[column.id].style }}>
													<div className="cells-and-actions">
														<div>
															<a
																href={row.cells[column.id].value}
																onClick={event => { event.preventDefault(); }}
																style={{
																	display: 'inline-block',
																	width: '100%',
																	textAlign: row.cells[column.id].align || column.align,
																}}
																contentEditable suppressContentEditableWarning
																ref={el => (cellRefs.current[row.cells[column.id].id] = el)}
																onBlur={() => handleChangeCell(sheetIndex, rowIndex, column.id)}
																className="cell-padding"
															></a>
														</div>
														<div>
															{row.cells[column.id].value && <React.Fragment>
																<a
																	href={row.cells[column.id].value}
																	target="_blank"
																><i className="fas fa-link"></i></a>&thinsp;
															</React.Fragment>}
														</div>
													</div>
												</td>;
												case 'function': return <td style={{ textAlign: column.align, ...column.style, ...row.cells[column.id].style }}>
													<div
														className="cell-padding"
														style={{
															textAlign: row.cells[column.id].align || column.align,
														}}
													>
														{window[ row.cells[column.id].value ] && window[ row.cells[column.id].value ](data, sheetIndex, column.id, rowIndex)}
													</div>
												</td>;
												default: return <td style={{...column.style, ...row.cells[column.id].style}}>
													<div
														contentEditable suppressContentEditableWarning
														ref={el => (cellRefs.current[row.cells[column.id].id] = el)}
														onBlur={() => handleChangeCell(sheetIndex, rowIndex, column.id)}
														className="cell-padding"
														style={{
															textAlign: row.cells[column.id].align || column.align,
														}}
													></div>
												</td>;
											}
										})()}
									</React.Fragment>
								)}
								{sheet.columns.length > 0 && (
									<th className="actions">
										{!sheet.columns.some(column => column.filter.enabled && column.filter.value !== '') && (
											<React.Fragment>
												<button
													title="add row above"
													className="action"
													onClick={() => handleAddRow(sheetIndex, rowIndex)}
												><i className="fas fa-upload"></i></button>
												<button
													title="add row bellow"
													className="action"
													onClick={() => handleAddRow(sheetIndex, rowIndex + 1)}
												><i className="fas fa-download"></i></button>
												<button
													title="move row up"
													className="action"
													onClick={() => handleMoveRow(sheetIndex, rowIndex, rowIndex - 1)}
													disabled={rowIndex === 0}
													style={{
														visibility: rowIndex === 0 ? 'hidden' : 'visible',
														display: sheet.body.length > 1 ? 'initial' : 'none',
													}}
												><i className="fas fa-arrow-up"></i></button>
												<button
													title="move row down"
													className="action"
													onClick={() => handleMoveRow(sheetIndex, rowIndex, rowIndex + 1)}
													disabled={(rowIndex + 1) === sheet.body.length}
													style={{
														visibility: (rowIndex + 1) === sheet.body.length ? 'hidden' : 'visible',
														display: sheet.body.length > 1 ? 'initial' : 'none',
													}}
												><i className="fas fa-arrow-down"></i></button>
											</React.Fragment>
										)}
										<button
											title="remove"
											className="action"
											onClick={() => handleRemoveRow(sheetIndex, rowIndex)}
										><i className="fas fa-trash"></i></button>
									</th>
								)}
							</tr>
						)}
					</tbody>
					<tfoot>
						<tr>
							{sheet.columns.length > 0 &&
								<td colSpan={sheet.columns.length}></td>
							}
							<th className="actions">
								{dirty[sheetIndex] &&
									<button
										title="save"
										className="action"
										onClick={() => handleSave()}
									>
										<span className="uppercase">save</span>
										&nbsp;
										<i className="fas fa-floppy-disk"></i>
									</button>
								}
							</th>
						</tr>
					</tfoot>
				</table>
			</React.Fragment>)}
		</React.Fragment>
	);
	// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
};
// =============================================================================
const App = () => (<React.Fragment>
	<TableEditor />
</React.Fragment>)
// -----------------------------------------------------------------------------
ReactDOM.createRoot(
	document.getElementById('root')
).render(<App />);
// -----------------------------------------------------------------------------
