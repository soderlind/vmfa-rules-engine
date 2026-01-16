/**
 * Vitest setup file.
 *
 * @package VmfaRulesEngine
 */

import { vi } from 'vitest';

// Mock window.vmfaRulesEngine global.
window.vmfaRulesEngine = {
	restUrl: 'http://example.com/wp-json/',
	nonce: 'test-nonce-123',
	folders: [
		{ id: 1, name: 'Photos', slug: 'photos' },
		{ id: 2, name: 'Documents', slug: 'documents' },
		{ id: 3, name: 'Videos', slug: 'videos' },
	],
	conditionTypes: [
		{
			value: 'filename_regex',
			label: 'Filename (Regex)',
			inputType: 'text',
			placeholder: '^IMG_',
		},
		{
			value: 'mime_type',
			label: 'MIME Type',
			inputType: 'select',
			options: [
				{ value: 'image/*', label: 'All Images' },
				{ value: 'image/jpeg', label: 'JPEG' },
				{ value: 'image/png', label: 'PNG' },
				{ value: 'video/*', label: 'All Videos' },
				{ value: 'application/pdf', label: 'PDF' },
			],
		},
		{
			value: 'dimensions',
			label: 'Dimensions',
			inputType: 'dimensions',
		},
		{
			value: 'file_size',
			label: 'File Size',
			inputType: 'filesize',
		},
		{
			value: 'exif_camera',
			label: 'EXIF Camera',
			inputType: 'text',
			placeholder: 'iPhone',
		},
		{
			value: 'exif_date',
			label: 'EXIF Date',
			inputType: 'daterange',
		},
		{
			value: 'author',
			label: 'Author',
			inputType: 'user',
		},
		{
			value: 'iptc_keywords',
			label: 'IPTC Keywords',
			inputType: 'text',
			placeholder: 'landscape, nature',
		},
	],
};

// Mock @wordpress/api-fetch.
vi.mock( '@wordpress/api-fetch', () => ( {
	default: vi.fn(),
} ) );

// Mock @wordpress/element.
vi.mock( '@wordpress/element', () => ( {
	useState: vi.fn( ( initial ) => {
		let state = initial;
		const setState = ( newState ) => {
			state = typeof newState === 'function' ? newState( state ) : newState;
		};
		return [ state, setState ];
	} ),
	useEffect: vi.fn( ( fn ) => fn() ),
	useCallback: vi.fn( ( fn ) => fn ),
	useRef: vi.fn( ( initial ) => ( { current: initial } ) ),
	useMemo: vi.fn( ( fn ) => fn() ),
	createRoot: vi.fn( () => ( {
		render: vi.fn(),
	} ) ),
	render: vi.fn(),
} ) );

// Mock @wordpress/i18n.
vi.mock( '@wordpress/i18n', () => ( {
	__: vi.fn( ( text ) => text ),
	_n: vi.fn( ( single, plural, count ) => ( count === 1 ? single : plural ) ),
	sprintf: vi.fn( ( format, ...args ) => {
		let result = format;
		args.forEach( ( arg, i ) => {
			result = result.replace( /%[sd]/, arg );
		} );
		return result;
	} ),
} ) );

// Mock @wordpress/components.
vi.mock( '@wordpress/components', () => ( {
	Button: ( { children, onClick, ...props } ) => {
		return { type: 'button', props: { ...props, onClick }, children };
	},
	Panel: ( { children, ...props } ) => {
		return { type: 'div', props: { className: 'panel', ...props }, children };
	},
	PanelBody: ( { children, title, ...props } ) => {
		return { type: 'div', props: { className: 'panel-body', ...props }, children, title };
	},
	PanelRow: ( { children, ...props } ) => {
		return { type: 'div', props: { className: 'panel-row', ...props }, children };
	},
	TextControl: ( { value, onChange, ...props } ) => {
		return { type: 'input', props: { ...props, value, onChange } };
	},
	SelectControl: ( { value, options, onChange, ...props } ) => {
		return { type: 'select', props: { ...props, value, options, onChange } };
	},
	ToggleControl: ( { checked, onChange, ...props } ) => {
		return { type: 'input', props: { ...props, type: 'checkbox', checked, onChange } };
	},
	Modal: ( { children, title, onRequestClose, ...props } ) => {
		return { type: 'div', props: { className: 'modal', ...props, title, onRequestClose }, children };
	},
	Spinner: () => {
		return { type: 'span', props: { className: 'spinner' } };
	},
	Notice: ( { children, status, ...props } ) => {
		return { type: 'div', props: { className: `notice notice-${ status }`, ...props }, children };
	},
	Flex: ( { children, ...props } ) => {
		return { type: 'div', props: { className: 'flex', ...props }, children };
	},
	FlexItem: ( { children, ...props } ) => {
		return { type: 'div', props: { className: 'flex-item', ...props }, children };
	},
	FlexBlock: ( { children, ...props } ) => {
		return { type: 'div', props: { className: 'flex-block', ...props }, children };
	},
	Card: ( { children, ...props } ) => {
		return { type: 'div', props: { className: 'card', ...props }, children };
	},
	CardBody: ( { children, ...props } ) => {
		return { type: 'div', props: { className: 'card-body', ...props }, children };
	},
	CardHeader: ( { children, ...props } ) => {
		return { type: 'div', props: { className: 'card-header', ...props }, children };
	},
	CheckboxControl: ( { checked, onChange, ...props } ) => {
		return { type: 'input', props: { ...props, type: 'checkbox', checked, onChange } };
	},
} ) );

// Mock @wordpress/icons.
vi.mock( '@wordpress/icons', () => ( {
	plus: 'plus-icon',
	trash: 'trash-icon',
	edit: 'edit-icon',
	check: 'check-icon',
	close: 'close-icon',
	dragHandle: 'drag-handle-icon',
	chevronUp: 'chevron-up-icon',
	chevronDown: 'chevron-down-icon',
} ) );

// Mock @dnd-kit packages.
vi.mock( '@dnd-kit/core', () => ( {
	DndContext: ( { children } ) => children,
	closestCenter: vi.fn(),
	KeyboardSensor: vi.fn(),
	PointerSensor: vi.fn(),
	useSensor: vi.fn(),
	useSensors: vi.fn( () => [] ),
} ) );

vi.mock( '@dnd-kit/sortable', () => ( {
	SortableContext: ( { children } ) => children,
	verticalListSortingStrategy: 'vertical',
	useSortable: vi.fn( () => ( {
		attributes: {},
		listeners: {},
		setNodeRef: vi.fn(),
		transform: null,
		transition: null,
		isDragging: false,
	} ) ),
	arrayMove: vi.fn( ( arr, from, to ) => {
		const result = [ ...arr ];
		const [ removed ] = result.splice( from, 1 );
		result.splice( to, 0, removed );
		return result;
	} ),
} ) );

vi.mock( '@dnd-kit/utilities', () => ( {
	CSS: {
		Transform: {
			toString: vi.fn( () => '' ),
		},
	},
} ) );
