<?php
/**
 * POMOEditor Projects Collection
 *
 * @package POMOEditor
 * @subpackage Structures
 *
 * @since 1.0.0
 */

namespace POMOEditor;

/**
 * The Projects Collection
 *
 * An array-like system for storing multiple Project objects.
 * Works like an array within `foreach` loops, and includes
 * methods for sorting, filtering, and searching for projects.
 *
 * @package POMOEditor
 * @subpackage Structures
 *
 * @api
 *
 * @since 1.0.0
 */
final class Projects implements \Iterator {
	// =========================
	// ! Properties
	// =========================

	/**
	 * The current position in the array.
	 *
	 * @internal
	 *
	 * @since 1.0.0
	 *
	 * @var int
	 */
	protected $position = 0;

	/**
	 * The array of Project objects.
	 *
	 * @internal
	 *
	 * @since 1.0.0
	 *
	 * @var array
	 */
	protected $items = array();

	/**
	 * A reference list of modded versions of projects.
	 *
	 * @internal
	 *
	 * @since 1.3.0
	 *
	 * @var array
	 */
	protected $modded_versions = array();

	// =========================
	// ! Iterator Methods
	// =========================

	/**
	 * Rewind iterator to the first element.
	 *
	 * @since 1.0.0
	 */
	public function rewind() {
		$this->position = 0;
	}

	/**
	 * Return to the current element.
	 *
	 * @since 1.0.0
	 *
	 * @return mixed The current element.
	 */
	public function current() {
		return $this->items[ $this->position ];
	}

	/**
	 * Return to the key of the current element.
	 *
	 * @since 1.0.0
	 *
	 * @return int|string The current key.
	 */
	public function key() {
		return $this->position;
	}

	/**
	 * Advance to the next element.
	 *
	 * @since 1.0.0
	 *
	 * @return mixed The next element.
	 */
	public function next() {
		++$this->position;
	}

	/**
	 * Check if current position is valid.
	 *
	 * @since 1.0.0
	 *
	 * @return bool Wether or not the position is valid.
	 */
	public function valid() {
		return isset( $this->items[ $this->position ] );
	}

	/**
	 * Get the length of the array.
	 *
	 * @since 1.0.0
	 *
	 * @return int The length of the array.
	 */
	public function count() {
		return count( $this->items );
	}

	// =========================
	// ! Methods
	// =========================

	/**
	 * Setup the collection and add any projects passed.
	 *
	 * @since 1.0.0
	 *
	 * @param array $projects Optional. A list of projects to add.
	 */
	public function __construct( $projects = array() ) {
		if ( is_array( $projects ) && ! empty ( $projects ) ) {
			foreach ( $projects as $project ) {
				$this->add( $project, false );
			}
		}

		// Sort the collection
		$this->sort();

		// Reset the position
		$this->position = 0;
	}

	/**
	 * Scan a directory for projects, add them.
	 *
	 * @since 1.3.0 Added check to make sure directory exists, auto-sorting.
	 * @since 1.2.0 Added PME content directory to scan list.
	 * @since 1.0.0
	 *
	 * @param string $dir The directory (should be absolute). Defaults to languages, themes, and plugins.
	 */
	public function scan( $dir = null ) {
		if ( is_null( $dir ) ) {
			// Scan the PME content, languages, themes, and plugins directories
			$this->scan( PME_CONTENT_DIR );
			$this->scan( WP_CONTENT_DIR . '/languages' );
			$this->scan( WP_CONTENT_DIR . '/themes' );
			$this->scan( WP_CONTENT_DIR . '/plugins' );
			$this->sort();
			return;
		}


		// Abort if the directory doesn't exist.
		if ( ! file_exists( $dir ) || ! is_dir( $dir ) ) {
			return;
		}

		$skip = ! is_path_permitted( $dir );

		foreach ( scandir( $dir ) as $file ) {
			if ( substr( $file, 0, 1 ) == '.' ) {
				continue;
			}

			$path = "$dir/$file";
			// If it's a directory (but not a link) scan it
			if ( is_dir( $path ) && ! is_link( $path ) ) {
				$this->scan( $path );
			} else
			// If it's a file with the .po extension, add it unless $skip is set
			if ( is_file( $path ) && substr( $file, -3 ) === '.po' && ! $skip ) {
				$this->add( $path );
			}
		}
	}

	/**
	 * Get a list of all types present.
	 *
	 * @since 1.0.0
	 *
	 * @return array A list of types.
	 */
	public function types() {
		$types = array();
		foreach ( $this as $project ) {
			$type = $project->package( 'type' );
			if ( ! isset( $types[ $type ] ) ) {
				$types[ $type ] = $type;
			}
		}
		asort( $types );
		return $types;
	}

	/**
	 * Get a list of all packages present.
	 *
	 * @since 1.0.0
	 *
	 * @return array A list of packages.
	 */
	public function packages() {
		$packages = array();
		foreach ( $this as $project ) {
			$name = $project->package( 'name' );
			$slug = $project->package( 'slug' );
			if ( ! isset( $packages[ $slug ] ) ) {
				$packages[ $slug ] = $name;
			}
		}
		asort( $packages );
		return $packages;
	}

	/**
	 * Get a list of all languages present.
	 *
	 * @since 1.0.0
	 *
	 * @return array A list of languages.
	 */
	public function languages() {
		$languages = array();
		foreach ( $this as $project ) {
			$name = $project->language( false );
			$slug = $project->language( true );
			if ( ! isset( $packages[ $slug ] ) ) {
				$languages[ $slug ] = $name;
			}
		}
		asort( $languages );
		return $languages;
	}

	/**
	 * Sort the object index by package property.
	 *
	 * @since 1.3.0 Actually implemented sorting by package property.
	 * @since 1.0.0
	 *
	 * @param string $field Optional. The field to sort by (defaults to list_order).
	 * @param string $order Optional. Which way to sort (defaults to ascending).
	 *
	 * @return self.
	 */
	public function sort( $field = 'name', $order = 'asc' ) {
		usort( $this->items, function( $a, $b ) use ( $field ) {
			$a_value = $a->package( $field );
			$b_value = $b->package( $field );

			if ( $a_value == $b_value ) {
				return $a->file() > $b->file() ? 1 : 0;
			}

			return $a_value > $b_value ? 1 : -1;
		} );

		// If not in ascending order, reverse the array
		if ( $order != 'asc' ) {
			$this->items = array_reverse( $this->items );
		}

		return $this;
	}

	/**
	 * Return a filtered copy of the collection.
	 *
	 * @since 1.0.0
	 *
	 * @param string $filter Optional. The property to filter by.
	 * @param string $value  Optional. A specific value to filter by (defaults to TRUE).
	 *
	 * @return POMOEditor\Projects A new collection of projects
	 */
	public function filter( $filter = null, $value = null ) {
		return $this;

		// No filter? Return original
		if ( is_null( $filter ) ) {
			return $this;
		}

		// No value? Assume true
		if ( is_null( $value ) ) {
			$value = true;
		}

		$filtered = new static;
		foreach ( $this as $project ) {
			if ( $project->$filter === $value ) {
				$filtered->add( $project, false );
			}
		}

		return $filtered;
	}

	/**
	 * Retrieve a project from the index.
	 *
	 * @since 1.0.0
	 *
	 * @param int|string $value A value to retrieve the project by.
	 * @param string     $field Optional. The field to search in (defaults to id or slug).
	 *
	 * @return bool|Project The project if found (false if not).
	 */
	public function get( $value, $field = null ) {
		// Guess $field based on nature of $project if not provided
		if ( is_null( $field ) ) {
			// File by default
			$field = 'file';
		}

		// If $field is "@", return the entry in the $items array for that index
		if ( $field == '@' ) {
			return isset( $this->items[ $value ] ) ? $this->items[ $value ] : false;
		}

		// Loop through all projects and return the first match
		foreach ( $this->items as $project ) {
			if ( $project->$field == $value ) {
				return $project;
			}
		}

		return false;
	}

	/**
	 * Alias of get(); retrieves the project at the specific array index.
	 *
	 * @since 1.0.0
	 *
	 * @see Projects::get() for details.
	 *
	 * @param int $index The index to get the item at.
	 *
	 * @return bool|Project The project if found (false if not).
	 */
	public function nth( $index ) {
		return $this->get( $index, '@' );
	}

	/**
	 * Get the index of the first project matching the provided ID/slug.
	 *
	 * @since 1.0.0
	 *
	 * @param mixed $project The project object, ID or slug.
	 *
	 * @return int|bool The index if found, false otherwise.
	 */
	public function find( $project ) {
		// Get the project object
		if ( ! is_a( $project, __NAMESPACE__ . '\Project' ) ) {
			$project = $this->get( $project );
		}

		// If not found, fail
		if ( ! $project ) {
			return false;
		}

		// Loop through all projects and return index of first match
		foreach ( $this->items as $index => $item ) {
			if ( $item->id == $project->id ) {
				return $index;
			}
		}
	}

	/**
	 * Add a project to the index.
	 *
	 * @since 1.3.0 Added check/skip of files with modded counterparts.
	 * @since 1.0.0
	 *
	 * @param array|Project $project The project to add.
	 * @param bool           $sort     Wether or not to sort after adding.
	 *
	 * @return self.
	 */
	public function add( $project, $sort = true ) {
		// Create new Project object if the data isn't an object
		if ( ! is_object( $project ) ) {
			$project = new Project( $project );
		}

		// Add to the index if successful and has no modded counterpart
		if ( $project && ! isset( $this->modded_items[ $project->file() ] ) ) {
			$this->items[] = $project;
		}

		// Also add to the modded listing if applicable
		if ( $project->is_modded() ) {
			$this->modded_items[ $project->file() ] = $project;
		}

		if ( $sort ) {
			// Sort the collection
			$this->sort();
		}

		return $this;
	}

	/**
	 * Remove a project from the index.
	 *
	 * @since 1.0.0
	 *
	 * @param int|string $project The ID or slug of the project to remove.
	 *
	 * @return self.
	 */
	public function remove( $project ) {
		// Get the object's index
		if ( $index = $this->find( $project ) ) {
			// Remove it
			unset( $this->items[ $index ] );
		}

		return $this;
	}

	/**
	 * Dump an array of the contained projects.
	 *
	 * @since 1.0.0
	 *
	 * @return array The dumped array of projects.
	 */
	public function dump() {
		$array = array();

		foreach ( $this as $item ) {
			$array[] = $item->dump();
		}

		return $array;
	}
}
