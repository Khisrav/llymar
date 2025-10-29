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
	const allDoorHandles = ref<ItemWithProperties[]>([])
	const canAccessDxf = ref(false)
	const selectedOpeningID = ref<number>(0)
	const useInputFields = ref(true)
	
	// Store initial state to track changes
	const initialState = ref<{
		sketch_vars: Record<number, Record<string, number[]>>,
		selectedDoorHandles: Record<number, number | undefined>,
		openings: { id: number, width: number, height: number }[]
	}>({
		sketch_vars: {},
		selectedDoorHandles: {},
		openings: []
	})
	
	// Sketch constraints
	const sketch_constraints: Record<string, Constraint> = {
		a: { start: 3, end: 25, default: 12, interval: 1 },
		b: { start: 14, end: 25, default: 19, interval: 1 },
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
			
			// Save the actual door handle ID to database
			const selectedHandleId = selectedDoorHandles.value[opening.id as number];
			const door_handle_item_id = selectedHandleId || null;
			
			return {
				...opening,
				...flatSketch,
				door_handle_item_id: door_handle_item_id,
				// Include updated dimensions
				width: opening.width,
				height: opening.height,
			};
		});
	})

	const availableDoorHandles = computed(() => {
		// Return all door handles that are not already in the order's doorHandles
		return allDoorHandles.value.filter(handle => 
			!doorHandles.value.some(orderHandle => orderHandle.id === handle.id)
		);
	})

	const hasUnsavedChanges = computed(() => {
		// Check if sketch_vars have changed
		for (const openingId in sketch_vars.value) {
			const currentVars = sketch_vars.value[openingId];
			const initialVars = initialState.value.sketch_vars[openingId];
			
			if (!initialVars) continue;
			
			for (const key in currentVars) {
				if (currentVars[key][0] !== initialVars[key][0]) {
					return true;
				}
			}
		}
		
		// Check if door handles have changed
		for (const openingId in selectedDoorHandles.value) {
			if (selectedDoorHandles.value[openingId] !== initialState.value.selectedDoorHandles[openingId]) {
				return true;
			}
		}
		
		// Check if opening dimensions have changed
		for (const opening of openings.value) {
			const initialOpening = initialState.value.openings.find(o => o.id === opening.id);
			if (initialOpening && (opening.width !== initialOpening.width || opening.height !== initialOpening.height)) {
				return true;
			}
		}
		
		return false;
	})

	// Actions
	const initializeStore = (initialData: {
		order: Order,
		openings: Opening[],
		doorHandles: ItemWithProperties[],
		allDoorHandles: ItemWithProperties[],
		// canAccessDxf: boolean
	}) => {
		order.value = initialData.order
		// Make sure openings are reactive
		openings.value = [...initialData.openings]
		doorHandles.value = initialData.doorHandles
		allDoorHandles.value = initialData.allDoorHandles
		// canAccessDxf.value = initialData.canAccessDxf

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

		// Store initial state for change tracking
		initialState.value = {
			sketch_vars: JSON.parse(JSON.stringify(sketch_vars.value)),
			selectedDoorHandles: { ...selectedDoorHandles.value },
			openings: openings.value.map(o => ({
				id: o.id as number,
				width: o.width as number,
				height: o.height as number
			}))
		};
		console.log(initialState.value);
	}

	const getHandleProperties = (item_id: number): { d: number; g: number; i: number; mp: number } => {
		// Look for the handle in all door handles (from database)
		const item = allDoorHandles.value.find((dh) => dh.id === item_id);

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
				//gap = 2 * 24 + 17 + 30 + 33 = 128
				//doorsGap["start"] = 30 + 33 = 63
				//middle = (7 * 13) / 7 = 13
				//edges = (7 * 13 - 13 * 6) / 2 = 6
				//z = 2872 / 8 = 359
				//temp = 359 + 6 = 365
				//shirinaStvorok[0] = 365 + 63 = 428
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

			// Update sketch vars with handle properties
			sketch_vars.value[openingId].d = [handleProps.d];
			sketch_vars.value[openingId].g = [handleProps.g];
			sketch_vars.value[openingId].i = [handleProps.i];
			sketch_vars.value[openingId].mp = [handleProps.mp];

			// Ensure the selected handle is part of the order handles list for grouping in UI
			const existsInOrder = doorHandles.value.some(dh => dh.id === doorHandleId);
			if (!existsInOrder) {
				const handle = allDoorHandles.value.find(dh => dh.id === doorHandleId);
				if (handle) {
					doorHandles.value.push(handle);
				}
			}

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

	const toggleInputMode = () => {
		useInputFields.value = !useInputFields.value;
	}

	const updateSketchVar = (openingId: number, key: string, value: number) => {
		if (!sketch_vars.value[openingId]) {
			sketch_vars.value[openingId] = {};
		}
		sketch_vars.value[openingId][key] = [value];
	}

	const updateOpeningDimension = (openingId: number, dimension: 'width' | 'height', value: number) => {
		const opening = openings.value.find(o => o.id === openingId);
		if (opening) {
			opening[dimension] = value;
		}
	}

	const addDoorHandleToOpening = (doorHandleId: number) => {
		const currentOpeningId = selectedOpeningID.value;
		if (currentOpeningId) {
			// Find the door handle in allDoorHandles
			const doorHandle = allDoorHandles.value.find(dh => dh.id === doorHandleId);
			
			if (doorHandle) {
				// Add to doorHandles if not already present
				const existingHandle = doorHandles.value.find(dh => dh.id === doorHandleId);
				if (!existingHandle) {
					doorHandles.value.push(doorHandle);
				}
				
				// Select the handle for the current opening
				selectDoorHandle(currentOpeningId, doorHandleId);
				toast.success("Ручка добавлена к проему");
			}
		}
	}

	const updateInitialState = () => {
		// Update the initial state to match the current state
		// This should be called after a successful save
		initialState.value = {
			sketch_vars: JSON.parse(JSON.stringify(sketch_vars.value)),
			selectedDoorHandles: { ...selectedDoorHandles.value },
			openings: openings.value.map(o => ({
				id: o.id as number,
				width: o.width as number,
				height: o.height as number
			}))
		};
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
		// if (!canAccessDxf.value) {
		// 	toast.warning("У вас нет доступа для скачивания DXF.");
		// 	return;
		// }

		// Check if there are unsaved changes
		if (hasUnsavedChanges.value) {
			toast.warning("Сначала сохраните изменения, затем скачайте файл.");
			return;
		}

		// Check if all openings have door handles selected
		const openingsWithoutHandles = openings.value.filter(opening => !selectedDoorHandles.value[opening.id as number]);
		if (openingsWithoutHandles.length > 0) {
			toast.error("Пожалуйста, выберите ручки для всех проемов перед скачиванием DXF.");
			return;
		}

		try {
			toast.info("Начинается загрузка DXF...");

			const response = await axios.post(
				`/orders/${order.value.id}/dxf`,
				{
					openings: combinedOpenings.value,
					saveData: false,
					order_id: order.value.id,
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
			console.error("Failed to download DXF:", error);
			if (axios.isAxiosError(error)) {
				toast.error("Ошибка при загрузке DXF файла.");
			} else {
				toast.error("Ошибка загрузки файла.");
			}
		}
	}

	const downloadPDF = async () => {
		// Check if there are unsaved changes
		if (hasUnsavedChanges.value) {
			toast.warning("Сначала сохраните изменения, затем скачайте файл.");
			return;
		}

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

	return {
		// State
		order,
		openings,
		doorHandles,
		allDoorHandles,
		// canAccessDxf,
		selectedOpeningID,
		useInputFields,
		sketch_constraints,
		sketch_vars,
		selectedDoorHandles,
		
		// Computed
		currentSketch,
		currentOpening,
		isSliderDisabled,
		combinedOpenings,
		availableDoorHandles,
		hasUnsavedChanges,
		
		// Actions
		initializeStore,
		getHandleProperties,
		getOpeningSketchDimensions,
		selectDoorHandle,
		clearSelectedDoorHandles,
		toggleInputMode,
		updateSketchVar,
		updateOpeningDimension,
		addDoorHandleToOpening,
		updateInitialState,
		downloadDXF,
		downloadPDF,
		
		// Function that can be overridden
		get saveAndClose() { return saveAndClose; },
		set saveAndClose(fn) { saveAndClose = fn; },
	}
}) 