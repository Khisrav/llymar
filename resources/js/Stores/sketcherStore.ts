import { defineStore } from 'pinia'
import { ref, computed, watch } from 'vue'
import { toast } from 'vue-sonner'
import axios from 'axios'
import { Item, ItemProperty, Opening, Order } from '../lib/types'

interface ItemWithProperties extends Item {
	item_properties: ItemProperty[];
}

interface Constraint {
	start: number;
	end: number;
	default: number;
	interval: number;
}

export const useSketcherStore = defineStore('sketcherStore', () => {
	// State
	const order = ref<Order>({} as Order)
	const openings = ref<Opening[]>([])
	const doorHandles = ref<ItemWithProperties[]>([])
	const canAccessDxf = ref(false)
	const selectedOpeningID = ref<number>(0)
	const customHandleIdCounter = ref(1)
	
	// Sketch constraints
	const sketch_constraints: Record<string, Constraint> = {
		a: { start: 3, end: 25, default: 14, interval: 1 },
		b: { start: 14, end: 25, default: 17, interval: 1 },
		d: { start: 8, end: 55, default: 6, interval: 1 },
		e: { start: 20, end: 80, default: 30, interval: 1 },
		f: { start: 5, end: 20, default: 14, interval: 1 },
		g: { start: 30, end: 80, default: 55, interval: 1 },
		i: { start: 20, end: 1000, default: 550, interval: 1 },
		mp: { start: 0, end: 1000, default: 0, interval: 1 },
	}

	const sketch_vars = ref<Record<number, Record<string, number[]>>>({})
	const selectedDoorHandles = ref<Record<number, number | undefined>>({})

	// Computed properties
	const currentSketch = computed(() => sketch_vars.value[selectedOpeningID.value] || {})
	const currentOpening = computed(() => openings.value.find((opening) => opening.id === selectedOpeningID.value))

	const isSliderDisabled = computed(() => {
		const selectedHandleId = selectedDoorHandles.value[selectedOpeningID.value];
		if (!selectedHandleId) return false;

		try {
			const handleProps = getHandleProperties(selectedHandleId);
			return selectedHandleId >= 0 && (handleProps.mp !== 0 || handleProps.d !== 0);
		} catch (error) {
			return false;
		}
	})

	const combinedOpenings = computed(() => {
		return openings.value.map((opening) => {
			const updatedSketch = sketch_vars.value[opening.id as number] || {};
			const flatSketch: Record<string, number> = {};
			for (const key in updatedSketch) {
				flatSketch[key] = updatedSketch[key][0];
			}
			
			// For custom handles (negative IDs), save null to database
			// For regular handles, save the actual ID
			const selectedHandleId = selectedDoorHandles.value[opening.id as number];
			const door_handle_item_id = selectedHandleId && selectedHandleId > 0 ? selectedHandleId : null;
			
			return {
				...opening,
				...flatSketch,
				door_handle_item_id: door_handle_item_id,
			};
		});
	})

	// Actions
	const initializeStore = (initialData: {
		order: Order,
		openings: Opening[],
		doorHandles: ItemWithProperties[],
		canAccessDxf: boolean
	}) => {
		order.value = initialData.order
		openings.value = initialData.openings
		doorHandles.value = initialData.doorHandles
		canAccessDxf.value = initialData.canAccessDxf

		// Initialize sketch variables and selected door handles
		openings.value.forEach((opening: Opening) => {
			sketch_vars.value[opening.id as number] = {
				a: [opening.a as number],
				b: [opening.b as number],
				d: [opening.d as number],
				e: [opening.e as number],
				f: [opening.f as number],
				g: [opening.g as number],
				i: [opening.i as number],
				mp: [opening.mp as number],
			};
			selectedDoorHandles.value[opening.id as number] = opening.door_handle_item_id;
		});

		// Set initial selected opening
		if (openings.value.length > 0) {
			selectedOpeningID.value = openings.value[0].id as number;
		}

		// Load custom door handles from localStorage
		loadCustomDoorHandles();

		// Apply door handle properties to sketch_vars for each opening
		openings.value.forEach((opening) => {
			const handleId = selectedDoorHandles.value[opening.id as number] || opening.door_handle_item_id;

			if (handleId) {
				try {
					const handleProps = getHandleProperties(handleId);
					sketch_vars.value[opening.id as number] = {
						...sketch_vars.value[opening.id as number],
						d: [handleProps.d],
						g: [handleProps.g],
						i: [handleProps.i],
						mp: [handleProps.mp],
					};
				} catch (error) {
					console.error(`Error applying handle properties for opening ${opening.id}:`, error);
				}
			}
		});
	}

	const loadCustomDoorHandles = () => {
		try {
			const saved = localStorage.getItem("customDoorHandles");
			if (saved) {
				const customHandles = JSON.parse(saved);
				// Add custom handles to the doorHandles array
				doorHandles.value.push(...customHandles);
			}
			
			// Load and increment the counter to ensure uniqueness
			const savedCounter = localStorage.getItem("customHandleIdCounter");
			if (savedCounter) {
				customHandleIdCounter.value = parseInt(savedCounter);
			}
		} catch (error) {
			console.error("Error loading custom door handles:", error);
		}
	}

	// Clear localStorage data related to custom handles only
	const clearStoredData = () => {
		try {
			localStorage.removeItem("customDoorHandles");
			localStorage.removeItem("customHandleIdCounter");
		} catch (error) {
			console.error("Error clearing stored data:", error);
		}
	}

	const saveCustomDoorHandles = () => {
		try {
			const customHandles = doorHandles.value.filter((handle) => (handle.id ?? 0) < 0);
			localStorage.setItem("customDoorHandles", JSON.stringify(customHandles));
			// Save the counter as well
			localStorage.setItem("customHandleIdCounter", customHandleIdCounter.value.toString());
		} catch (error) {
			console.error("Error saving custom door handles:", error);
		}
	}

	const getHandleProperties = (item_id: number): { d: number; g: number; i: number; mp: number } => {
		const item = doorHandles.value.find((dh) => dh.id === item_id);

		if (!item) {
			throw new Error("Item not found");
		}

		const getPropertyValue = (name: string): number => {
			const property = item.item_properties.find((p) => p.name === name);
			if (!property) {
				throw new Error(`Property ${name} not found`);
			}
			const value = parseFloat(property.value);
			if (isNaN(value)) {
				throw new Error(`Property ${name} value is not a valid number`);
			}
			// Only apply constraints if they exist for this property
			if (sketch_constraints[name]) {
				return Math.min(Math.max(value, sketch_constraints[name].start), sketch_constraints[name].end);
			}
			return value;
		};

		return {
			d: getPropertyValue("d"),
			g: getPropertyValue("g"),
			i: getPropertyValue("i"),
			mp: getPropertyValue("MP"),
		};
	}

	const getOpeningSketchDimensions = (doorIndex: number) => {
		if (!currentOpening.value) return { width: 0, height: 0 };

		let gap = currentOpening.value.type == "center" ? sketch_vars.value[selectedOpeningID.value].a[0] + sketch_vars.value[selectedOpeningID.value].b[0] + sketch_vars.value[selectedOpeningID.value].e[0] + sketch_vars.value[selectedOpeningID.value].g[0] + 3 : 2 * sketch_vars.value[selectedOpeningID.value].a[0] + sketch_vars.value[selectedOpeningID.value].b[0] + sketch_vars.value[selectedOpeningID.value].e[0] + sketch_vars.value[selectedOpeningID.value].g[0];
		let doorsGap = {
			start: sketch_vars.value[selectedOpeningID.value].e[0] + sketch_vars.value[selectedOpeningID.value].g[0],
			end: sketch_vars.value[selectedOpeningID.value].b[0],
		};
		let overlaps = currentOpening.value.doors / (currentOpening.value.type == "center" ? 2 : 1) - 1;
		let middle = Math.floor((overlaps * 13) / (currentOpening.value.doors / (currentOpening.value.type == "center" ? 2 : 1)));
		let edges = currentOpening.value.type == "center" ? Math.floor((overlaps * 13 - middle * (currentOpening.value.doors / 2 - 2)) / 2) : Math.floor((overlaps * 13 - middle * (currentOpening.value.doors - 2)) / 2);
		let y = currentOpening.value.width / (currentOpening.value.type == "center" ? 2 : 1) - gap;
		let z = y / (currentOpening.value.doors / (currentOpening.value.type == "center" ? 2 : 1));
		let shirinaStvorok = [];

		if (currentOpening.value.type == "center") {
			for (let i = 1; i <= currentOpening.value.doors / 2; i++) {
				let temp = z + (i == currentOpening.value.doors / 2 || i == 1 ? edges : middle);
				if (i == 1) {
					temp += doorsGap["end"];
				} else if (i == currentOpening.value.doors / 2) {
					temp += doorsGap["start"];
				}

				shirinaStvorok[i] = Math.floor(temp);
				shirinaStvorok[currentOpening.value.doors - i + 1] = Math.floor(temp);
			}
		} else if (currentOpening.value.type == "left" || currentOpening.value.type == "right") {
			for (let i = 1; i <= currentOpening.value.doors; i++) {
				let temp = z + (i == currentOpening.value.doors || i == 1 ? edges : middle);
				if (i == 1) {
					temp += doorsGap["end"];
				} else if (i == currentOpening.value.doors) {
					temp += doorsGap["start"];
				}

				shirinaStvorok[i] = Math.floor(temp);
			}
		}

		return {
			width: shirinaStvorok[doorIndex],
			height: currentOpening.value.height - 103,
		};
	}

	const selectDoorHandle = (openingId: number, doorHandleId: number) => {
		selectedDoorHandles.value[openingId] = doorHandleId;

		try {
			const handleProps = getHandleProperties(doorHandleId);

			// If item properties are 0, use opening's properties
			sketch_vars.value[openingId].d = [handleProps.d || sketch_vars.value[openingId].d[0]];
			sketch_vars.value[openingId].g = [handleProps.g || sketch_vars.value[openingId].g[0]];
			sketch_vars.value[openingId].i = [handleProps.i || sketch_vars.value[openingId].i[0]];
			sketch_vars.value[openingId].mp = [handleProps.mp || sketch_vars.value[openingId].mp[0]];

			console.log(sketch_vars.value[openingId]);
		} catch (error) {
			console.error("Error setting door handle properties:", error);
		}
	}

	const clearSelectedDoorHandles = (id?: number) => {
		if (!id) {
			selectedDoorHandles.value = {};
		} else {
			delete selectedDoorHandles.value[id];
		}
	}

	const addCustomDoorHandle = () => {
		const uniqueId = -(Date.now() + customHandleIdCounter.value);
		customHandleIdCounter.value++;
		
		const newHandle = {
			id: uniqueId,
			name: "Своя ручка",
			img: "",
			purchase_price: 0,
			discount: 0,
			retail_price: 0,
			unit: "",
			category_id: 0,
			item_properties: [
				{
					id: -(Date.now() + 1000 + customHandleIdCounter.value),
					item_id: uniqueId,
					name: "d",
					value: "8",
					created_at: null,
					updated_at: null,
				},
				{
					id: -(Date.now() + 1001 + customHandleIdCounter.value),
					item_id: uniqueId,
					name: "g",
					value: "0",
					created_at: null,
					updated_at: null,
				},
				{
					id: -(Date.now() + 1002 + customHandleIdCounter.value),
					item_id: uniqueId,
					name: "i",
					value: "0",
					created_at: null,
					updated_at: null,
				},
				{
					id: -(Date.now() + 1003 + customHandleIdCounter.value),
					item_id: uniqueId,
					name: "MP",
					value: "100",
					created_at: null,
					updated_at: null,
				},
			],
		};

		doorHandles.value.push(newHandle);
		saveCustomDoorHandles();
		toast.success("Своя ручка добавлена");
	}

	const removeCustomDoorHandle = (handleId: number) => {
		// Remove from doorHandles array
		const index = doorHandles.value.findIndex((handle) => handle.id === handleId);
		if (index > -1) {
			doorHandles.value.splice(index, 1);

			// Remove from selected handles if it was selected
			Object.keys(selectedDoorHandles.value).forEach((openingId) => {
				if (selectedDoorHandles.value[parseInt(openingId)] === handleId) {
					delete selectedDoorHandles.value[parseInt(openingId)];
				}
			});

			// Save custom handles to localStorage
			saveCustomDoorHandles();
		}
	}

	// This will be overridden by the component to use actual form submission
	let saveAndClose = (): Promise<boolean> => {
		return new Promise((resolve, reject) => {
			// This would need to be implemented with the actual form submission logic
			// For now, just resolve immediately
			toast("Сохранено");
			resolve(true);
		});
	}

	const downloadDXF = async () => {
		if (!canAccessDxf.value) {
			toast.warning("У вас нет доступа для скачивания DXF.");
			return;
		}

		// Check if all openings have door handles selected
		const openingsWithoutHandles = openings.value.filter(opening => !selectedDoorHandles.value[opening.id as number]);
		if (openingsWithoutHandles.length > 0) {
			toast.error("Пожалуйста, выберите ручки для всех проемов перед скачиванием DXF.");
			return;
		}

		try {
			toast.info("Сохранение данных...");
			await saveAndClose();

			toast.info("Данные сохранены. Начинается загрузка DXF...");

			const response = await axios.post(
				`/orders/${order.value.id}/dxf`,
				{
					openings: combinedOpenings.value,
					saveData: false,
				},
				{
					responseType: "blob",
				}
			);

			const fileBlob = new Blob([response.data], { type: "application/dxf" });
			const fileURL = URL.createObjectURL(fileBlob);

			const link = document.createElement("a");
			link.href = fileURL;
			link.setAttribute("download", `sketch_${order.value.id}.dxf`);
			document.body.appendChild(link);
			link.click();

			document.body.removeChild(link);
			URL.revokeObjectURL(fileURL);
			toast.success("DXF файл успешно загружен.");
		} catch (error) {
			console.error("Failed to save or download DXF:", error);
			if (axios.isAxiosError(error)) {
				toast.error("Ошибка при загрузке DXF файла.");
			} else {
				toast.error("Ошибка сохранения данных.");
			}
		}
	}

	const downloadPDF = async () => {
		// Check if all openings have door handles selected
		const openingsWithoutHandles = openings.value.filter(opening => !selectedDoorHandles.value[opening.id as number]);
		if (openingsWithoutHandles.length > 0) {
			toast.error("Пожалуйста, выберите ручки для всех проемов перед скачиванием PDF.");
			return;
		}

		try {
			toast.info("Подготовка PDF файла...");
			
			const response = await axios.post(
				"/app/order/sketch/download",
				{
					openings: combinedOpenings.value,
					saveData: true,
				},
				{
					responseType: "blob",
				}
			);

			const fileBlob = new Blob([response.data], { type: "application/pdf" });
			const fileURL = URL.createObjectURL(fileBlob);

			const link = document.createElement("a");
			link.href = fileURL;
			link.setAttribute("download", "sketch.pdf");
			document.body.appendChild(link);
			link.click();

			document.body.removeChild(link);
			URL.revokeObjectURL(fileURL);
			toast.success("PDF файл успешно загружен.");
		} catch (error) {
			console.error("Failed to download PDF:", error);
			toast.error("Ошибка при загрузке PDF файла.");
		}
	}

	// Watchers
	watch(
		() => doorHandles.value.filter((handle) => (handle.id ?? 0) < 0),
		() => {
			saveCustomDoorHandles();
		},
		{ deep: true }
	);

	watch(
		() => {
			const selectedHandleId = selectedDoorHandles.value[selectedOpeningID.value];
			if (selectedHandleId && selectedHandleId < 0) {
				return {
					handleId: selectedHandleId,
					properties: { ...sketch_vars.value[selectedOpeningID.value] }
				};
			}
			return null;
		},
		(newValue) => {
			if (newValue) {
				const handleIndex = doorHandles.value.findIndex(h => h.id === newValue.handleId);
				if (handleIndex !== -1) {
					const updatedHandle = {
						...doorHandles.value[handleIndex],
						item_properties: doorHandles.value[handleIndex].item_properties.map(prop => {
							// Map property names: MP in database -> mp in sketch_vars
							const sketchVarName = prop.name === "MP" ? "mp" : prop.name;
							
							if (newValue.properties[sketchVarName]) {
								return {
									...prop,
									value: newValue.properties[sketchVarName][0].toString()
								};
							}
							return { ...prop };
						})
					};
					
					doorHandles.value[handleIndex] = updatedHandle;
					saveCustomDoorHandles();
				}
			}
		},
		{ deep: true }
	)

	return {
		// State
		order,
		openings,
		doorHandles,
		canAccessDxf,
		selectedOpeningID,
		sketch_constraints,
		sketch_vars,
		selectedDoorHandles,
		
		// Computed
		currentSketch,
		currentOpening,
		isSliderDisabled,
		combinedOpenings,
		
		// Actions
		initializeStore,
		loadCustomDoorHandles,
		saveCustomDoorHandles,
		clearStoredData,
		getHandleProperties,
		getOpeningSketchDimensions,
		selectDoorHandle,
		clearSelectedDoorHandles,
		addCustomDoorHandle,
		removeCustomDoorHandle,
		downloadDXF,
		downloadPDF,
		
		// Function that can be overridden
		get saveAndClose() { return saveAndClose; },
		set saveAndClose(fn) { saveAndClose = fn; },
	}
}) 