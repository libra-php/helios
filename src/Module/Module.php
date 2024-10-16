<?php

namespace Helios\Module;

use App\Models\Audit;
use App\Models\File;
use App\Models\Session;
use Error, Exception;
use Helios\Model\Model;
use Helios\View\{Flash, Form, IView, Table};
use PDO;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class Module
{
    protected ?int $id = null;
    protected bool $has_edit = true;
    protected bool $has_create = true;
    protected bool $has_delete = true;
    protected bool $has_cancel = true;
    protected bool $export_csv = true;
    protected string $model;
    protected array $rules = [];

    private array $table = [];
    private array $format = [];
    private array $where = [];
    private array $group_by = [];
    private array $having = [];
    private string $order_by = "";
    private string $sort = "ASC";
    private array $params = [];
    private array $filter_links = [];
    private array $searchable = [];
    private string $search_term = "";
    private string $date_column = "";
    private string $date_filter_start = "";
    private ?string $date_filter_end = null;
    private int $filter_link = 0;
    private int $page = 1;
    private int $per_page = 25;
    private int $total_pages = 1;
    private array $page_options = [5, 10, 25, 50, 100, 200, 500];
    private int $total_results = 0;
    private array $form = [];
    private array $control = [];
    private array $defaults = [];

    public function view(IView $view, ?int $id = null): string
    {
        // Process incoming request
        $this->processRequest();

        // Set view data
        if ($view instanceof Table) {
            $view->setData($this->getTableData());
        } else if ($view instanceof Form) {
            if (!is_null($id)) {
                $this->id = $id;
                $view->setData($this->getFormData($id));
            } else {
                $view->setData($this->getFormData());
            }
        }

        // Module pass thru
        $view->setModule($this);

        // Record session
        Session::new([
            "request_uri" => request()->getUri(),
            "ip" => ip2long(request()->getClientIp()),
            "user_id" => user()->id,
            "module_id" => module()->id
        ]);

        // Render view
        return controller()?->render($view->getTemplate(), $view->getTemplateData());
    }

    public function processRequest(): void
    {
        if (!isset($this->model)) return;
        // Table view only
        $this->handleSearch();
        $this->handleDateFilter();
        $this->handleFilterCount();
        $this->handleFilterLinks();
        $this->handlePage();
        $this->handlePerPage();
        $this->handleOrderBy();
        $this->handleExportCsv();
    }

    public function hasEdit(): bool
    {
        return $this->has_edit;
    }

    public function hasEditPermission(int $id): bool
    {
        return $this->hasEdit();
    }

    public function hasCreate(): bool
    {
        return $this->has_create;
    }

    public function hasDelete(): bool
    {
        return $this->has_delete;
    }

    public function hasCancel(): bool
    {
        return $this->has_cancel;
    }

    public function hasDeletePermission(int $id): bool
    {
        return $this->hasDelete();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getKeyColumn(): string
    {
        if (!isset($this->model)) return '';
        return $this->model::get()->getKeyColumn();
    }

    public function getRules(): array
    {
        return $this->rules;
    }

    public function getTable(): array
    {
        $table = array_filter($this->table, fn($key) => substr($key, 0, 5) !== 'data_', ARRAY_FILTER_USE_KEY);
        return $this->stripAliases($table);
    }

    public function getForm(): array
    {
        return $this->stripAliases($this->form);
    }

    public function getFormat(): array
    {
        return $this->format;
    }

    public function getControl(): array
    {
        return $this->control;
    }

    public function create(array $data): ?Model
    {
        $model = $this->model::get();
        foreach ($data as $column => $value) {
            if ($value === '' || $value === 'NULL') {
                $data[$column] = null;
            }
            if (request()->files->has($column) && isset($this->control[$column]) && in_array($this->control[$column], ["file", "image"])) {
                $file_id = $this->handleFileUpload($column);
                if ($file_id !== false) {
                    $data[$column] = $file_id;
                    Flash::add("success", "File successfully uploaded");
                } else {
                    Flash::add("warning", "File upload failed");
                }
            }
        }
        // Created at should be set
        if (isset($model->created_at)) {
            $data['created_at'] = date("Y-m-d H:i:s");
        }
        $result = $model::new($data);
        if ($result) {
            foreach ($result->getAttributes() as $column => $value) {
                // Audit the new record
                Audit::new([
                    "user_id" => user()->id,
                    "table_name" => $this->model::get()->getTableName(),
                    "table_id" => $result->id,
                    "field" => $column,
                    "old_value" => null,
                    "new_value" => $value,
                    "tag" => "CREATE",
                ]);
            }
        }
        return $result;
    }

    public function save(int $id, array $data): bool
    {
        foreach ($data as $column => $value) {
            if ($value === '' || $value === 'NULL') {
                $data[$column] = null;
            }
            if (request()->files->has($column) && isset($this->control[$column]) && in_array($this->control[$column], ["file", "image"])) {
                $file_id = $this->handleFileUpload($column);
                if ($file_id !== false) {
                    $data[$column] = $file_id;
                    Flash::add("success", "File successfully uploaded");
                } else {
                    Flash::add("warning", "File upload failed");
                }
            }
            // TODO: does http foundation work with array of checkboxes?
            if (isset($_REQUEST["delete_file"])) {
                $to_delete = $_REQUEST["delete_file"];
                if (is_array($to_delete) && !empty($to_delete)) {
                    foreach ($to_delete as $i => $file_id) {
                        $file = File::find($file_id);
                        if ($file) {
                            // Also removes the asset
                            $result = $file->destroy();
                            if ($result) {
                                unset($_REQUEST["delete_file[$i]"]);
                                Flash::add("success", "File successfully deleted");
                            } else {
                                Flash::add("warning", "Failed to delete file");
                            }
                        }
                    }
                }
            }
        }
        $old = $this->model::find($id);
        // updated_at should be set
        if (isset($old->updated_at)) {
            $data['updated_at'] = date("Y-m-d H:i:s");
        }
        if ($old) {
            $result = $this->model::find($id)->save($data);
            if ($result) {
                foreach ($data as $column => $value) {
                    if ($old->$column != $value) {
                        // Audit the update
                        Audit::new([
                            "user_id" => user()->id,
                            "table_name" => $old->getTableName(),
                            "table_id" => $id,
                            "field" => $column,
                            "old_value" => $old->$column,
                            "new_value" => $value,
                            "tag" => "UPDATE",
                        ]);
                    }
                }
            }
            return $result;
        }
        return false;
    }

    public function delete(int $id): bool
    {
        $model = $this->model::find($id);
        if ($model) {
            $result = $model->destroy();
            if ($result) {
                // Audit the delete
                Audit::new([
                    "user_id" => user()->id,
                    "table_name" => $this->model::get()->getTableName(),
                    "table_id" => $id,
                    "field" => $this->model::get()->getKeyColumn(),
                    "old_value" => $id,
                    "new_value" => null,
                    "tag" => "DELETE",
                ]);
            }
            return $result;
        }
        return false;
    }

    public function getPagination(): array
    {
        if (!isset($this->model)) return [];
        $this->total_results = $this->getTotalResults();
        $this->total_pages = ceil($this->total_results / $this->per_page);

        return [
            "total_results" => $this->total_results,
            "total_pages" => $this->total_pages,
            "page" => $this->page,
            "per_page" => $this->per_page,
            "page_options" => $this->page_options,
        ];
    }

    public function getFilters(): array
    {
        return [
            "filter_links" => array_keys($this->filter_links),
            "filter_link" => $this->filter_link,
            "searchable" => $this->searchable,
            "search_term" => $this->search_term,
            "order_by" => $this->order_by,
            "sort" => $this->sort,
            "date_column" => $this->date_column,
            "date_filter_start" => $this->date_filter_start,
            "date_filter_end" => $this->date_filter_end,
        ];
    }

    public function getActions(): array
    {
        return [
            "export_csv" => $this->table ? $this->export_csv : false,
        ];
    }

    public function getCustomData(): array
    {
        return [];
    }

    protected function setSession(string $name, mixed $value): void
    {
        $module = module();
        $module_session = session()->get($module->path) ?? [];
        $module_session[$name] = $value;
        session()->set($module->path, $module_session);
    }

    protected function hasSession(string $name): bool
    {
        $module_session = session()->get(module()->path) ?? [];
        return key_exists($name, $module_session);
    }


    protected function getSession(string $name): mixed
    {
        $module_session = session()->get(module()->path) ?? [];
        return key_exists($name, $module_session) ? $module_session[$name] : null;
    }

    protected function removeSession(string $name): void
    {
        $module = module();
        $module_session = session()->get($module->path) ?? [];
        unset($module_session[$name]);
        session()->set($module->path, $module_session);
    }

    protected function form(string $header, string $attribute): Module
    {
        $this->form[$header] = $attribute;
        return $this;
    }

    protected function table(?string $header, string $attribute): Module
    {
        if (is_null($header)) {
            $header = 'data_' . count($this->table);
        }
        $this->table[$header] = $attribute;
        return $this;
    }

    protected function filterLink(string $title, string $condition): Module
    {
        $this->filter_links[$title] = $condition;
        return $this;
    }

    protected function removeForm(string $title): Module
    {
        unset($this->form[$title]);
        return $this;
    }

    protected function clearFilterLinks(): Module
    {
        $this->filter_links = [];
        return $this;
    }

    protected function search(string $column): Module
    {
        $this->searchable[] = $column;
        return $this;
    }

    protected function clearSearch(): Module
    {
        $this->searchable = [];
        return $this;
    }

    protected function where(string $where, ...$replacements): Module
    {
        $this->where[] = "($where)";
        foreach ($replacements as $replacement) {
            $this->params[] = $replacement;
        }
        return $this;
    }

    protected function groupBy(string $group_by): Module
    {
        $this->group_by[] = $group_by;
        return $this;
    }

    protected function having(string $having, ...$replacements): Module
    {
        $this->having[] = "($having)";
        foreach ($replacements as $replacement) {
            $this->params[] = $replacement;
        }
        return $this;
    }

    protected function defaultOrder(string $order_by): Module
    {
        $this->order_by = $order_by;
        return $this;
    }

    protected function dateColumn(string $column): Module
    {
        $this->date_column = $column;
        return $this;
    }

    protected function defaultSort(string $sort): Module
    {
        if (!in_array(strtoupper($sort), ["ASC", "DESC"])) {
            throw new Error("incorrect sort");
        }
        $this->sort = $sort;
        return $this;
    }

    protected function format(string $column, mixed $callback)
    {
        $this->format[$column] = $callback;
        return $this;
    }

    protected function control(string $column, mixed $callback)
    {
        $this->control[$column] = $callback;
        return $this;
    }

    protected function default(string $column, mixed $value)
    {
        $this->defaults[$column] = $value;
    }

    private function getTableData(): array
    {
        if (!isset($this->model)) return [];

        $page = max(($this->page - 1) * $this->per_page, 0);

        $results = $this->model::search($this->table)
            ->where($this->where)
            ->groupBy($this->group_by)
            ->having($this->having)
            ->orderBy([$this->order_by . ' ' . $this->sort])
            ->offset($page)
            ->limit($this->per_page)
            ->params($this->params)
            ->execute()->fetchAll(PDO::FETCH_ASSOC);

        return $results;
    }

    private function getFormData(?int $id = null): bool|array
    {
        if (!isset($this->model)) return [];

        if ($id) {
            // Edit
            $key = $this->model::get()->getKeyColumn();
            $this->where("$key = ?", $id);
            $data = $this->model::search($this->form)
                ->where($this->where)
                ->params($this->params)
                ->execute()->fetch(PDO::FETCH_ASSOC);
        } else {
            // Create
            $columns = $this->model::get()->getColumns();
            $data = array_fill_keys($columns, null);
            foreach ($data as $column => $value) {
                if (isset($this->defaults[$column])) {
                    $data[$column] = $this->defaults[$column];
                }
            }
        }
        // If the value is present in the request, use it
        if (is_array($data)) {
            foreach ($data as $column => $value) {
                $old = request()->request->get($column);
                if ($old) {
                    $data[$column] = $old;
                }
            }
        } else {
            $data = [];
        }
        return $data;
    }

    private function getTotalResults(): int
    {
        return $this->model::search($this->table)
            ->where($this->where)
            ->groupBy($this->group_by)
            ->having($this->having)
            ->params($this->params)
            ->execute()
            ->rowCount();
    }

    private function stripAliases(array $data): array
    {
        $out = [];
        foreach ($data as $title => $column) {
            $arr = explode(" as ", $column);
            $out[$title] = end($arr);
        }
        return $out;
    }

    /**
     * Handle a file upload request
     * Stored in uploads directory
     * @param string $key in request
     * @return ?int File id
     */
    private function handleFileUpload(string $key): string|bool
    {
        // Get the uploaded file
        $file = request()->files->get($key);

        // Check if a file was uploaded
        if ($file instanceof UploadedFile && $file->isValid()) {
            // Check if temp file exists
            $tmp_file = $file->getPathname();
            if (!file_exists($tmp_file)) {
                throw new \Exception("Temporary file does not exist: " . $tmp_file);
            }

            // Generate a unique name for the file
            $file_name = md5(uniqid()) . '.' . $file->getClientOriginalExtension();

            // Check if the uploads directory exists and is writable
            $uploads_directory = config("paths.uploads");
            if (!is_dir($uploads_directory) || !is_writable($uploads_directory)) {
                throw new \Exception("Uploads directory does not exist or is not writable: " . $uploads_directory);
            }

            // File metadata
            $original_name = $file->getClientOriginalName();
            $mime_type = $file->getMimeType();
            $size = $file->getSize();

            // Move the file to uploads
            $file->move($uploads_directory, $file_name);

            // Save file metadata to database
            $file = File::new([
                "filename" => $file_name,
                "original_name" => $original_name,
                "mime_type" => $mime_type,
                "size" => $size,
            ]);

            return $file ? $file->id : false;
        }
        return false;
    }

    /**
     * Handle page request
     * Used in table pagination
     */
    private function handlePage(): void
    {
        if (request()->query->has("page")) {
            $page = request()->query->get("page");
        } else {
            $page = $this->getSession("page") ?? $this->page;
        }
        $this->setPage($page);
    }

    /**
     * Handle per page request
     * How many rows shown per page
     */
    private function handlePerPage(): void
    {
        if (request()->query->has("per_page")) {
            $per_page = request()->query->get("per_page");
            $this->setPage(1);
        } else {
            $per_page = $this->getSession("per_page") ?? $this->per_page;
        }
        $this->setPerPage($per_page);
    }

    /**
     * Handle export CSV request
     * Exports table data to CSV format
     */
    private function handleExportCsv(): void
    {
        if (request()->query->has("export_csv")) {
            $table = $this->getTable();
            $file_name = module()->path . "_" . time() . '.csv';
            header("Content-Type: text/csv");
            header("Content-Disposition: attachment; filename=$file_name");
            $fp = fopen("php://output", "wb");
            $headers = array_keys($table);
            fputcsv($fp, $headers);
            $this->per_page = 1000;
            $this->page = 1;
            $this->total_results = $this->getTotalResults();
            $this->total_pages = ceil($this->total_results / $this->per_page);
            while ($this->page <= $this->total_pages) {
                $results = $this->getTableData();
                foreach ($results as $item) {
                    $row = []; 
                    foreach ($item as $column => $value) {
                        if (in_array($column, $table)) {
                            $row[$column] = $value;
                        }
                    }
                    $values = array_values($row);
                    fputcsv($fp, $values);
                }
                $this->page++;
            }
            fclose($fp);
            exit();
        }
    }

    /**
     * Handle search request
     * Filters table columns on search term
     */
    private function handleSearch(): void
    {
        if (request()->query->has("search_term")) {
            $term = request()->query->get("search_term");
            $this->setPage(1);
        } else {
            $term = $this->getSession("search_term") ?? $this->search_term;
        }
        $this->setSearchTerm($term);
    }

    /**
     * Handle date filter request
     */
    private function handleDateFilter(): void
    {
        if (request()->query->has("date_filter_start")) {
            $start = request()->query->get("date_filter_start");
            $end = request()->query->get("date_filter_end");
            $this->setPage(1);
        } else {
            $start = $this->getSession("date_filter_start") ?? $this->date_filter_start;
            $end = $this->getSession("date_filter_end") ?? $this->date_filter_end;
        }
        if (request()->query->has("clear_date")) {
            if (request()->query->get("clear_date") === "start") {
                $this->removeSession("date_filter_start");
                $start = null;
            } else {
                $this->removeSession("date_filter_end");
                $end = null;
            }
        }
        if ($start) {
            $this->setDateFilter($start, $end);
        }
    }

    /**
     * Handle fitler count request
     * The count shown in table link filter
     */
    private function handleFilterCount(): void
    {
        if (request()->query->has("filter_count")) {
            $filters = array_values($this->filter_links);
            $index = request()->query->get("filter_count");
            if (key_exists($index, $filters)) {
                $this->per_page = 1000;
                $this->where($filters[$index]);
                $count = $this->getTotalResults();
                echo $count >= 1000 ? '1000+' : $count;
                exit;
            }
            echo 0;
            exit;
        }
    }

    /**
     * Handle filter link request
     * Links above the table with counts
     */
    private function handleFilterLinks(): void
    {
        if (request()->query->has("filter_link")) {
            $this->setPage(1);
            $index = request()->query->get("filter_link");
        } else {
            $index = $this->getSession("filter_link") ?? $this->filter_link;
        }
        $this->setFilterLink($index);
    }

    /**
     * Handle order by and sorting requests
     * Sorts table column ASC/DESC
     */
    private function handleOrderBy(): void
    {
        if (request()->query->has("order_by")) {
            $order_by = request()->query->get("order_by");
        } else {
            $order_by = $this->getSession("order_by") ?? $this->model::get()->getKeyColumn();
        }
        if (request()->query->has("sort")) {
            $sort = request()->query->get("sort");
        } else {
            $sort = $this->getSession("sort") ?? $this->sort;
        }
        $this->setOrderBy($order_by);
        $this->setSort($sort);
    }

    private function setPage(int $page): void
    {
        $this->page = $page;
        $this->setSession("page", $page);
    }

    private function setPerPage(int $per_page): void
    {
        $this->per_page = $per_page;
        $this->setSession("per_page", $per_page);
    }

    private function setFilterLink(int $index): void
    {
        $filters = array_values($this->filter_links);
        if (isset($filters[$index])) {
            $this->filter_link = $index;
            $this->where($filters[$index]);
            $this->setSession("filter_link", $index);
        }
    }

    private function setOrderBy(string $order_by): void
    {
        $this->order_by = $order_by;
        $this->setSession("order_by", $order_by);
    }

    private function setSort(string $sort): void
    {
        $this->sort = $sort;
        $this->setSession("sort", $sort);
    }

    private function setDateFilter(string $start, ?string $end = null): void
    {
        if ($this->date_column && $start) {
            $this->date_filter_start = $start;
            $this->date_filter_end = $end;
            $this->where("{$this->date_column} >= ?", $start);
            if ($end) {
                $this->where("{$this->date_column} <= ?", $end);
            }
            $this->setSession("date_filter_start", $start);
            $this->setSession("date_filter_end", $end);
        }
    }

    private function setSearchTerm(string $term): void
    {
        if (trim($term)) {
            $this->search_term = $term;
            $clause = [];
            foreach ($this->searchable as $column) {
                $clause[] = "($column LIKE ?)";
            }
            $replacements = array_fill(0, count($clause), "%{$this->search_term}%");
            $this->having(implode(" OR ", $clause), ...$replacements);
        }
        $this->setSession("search_term", $this->search_term);
    }
}
