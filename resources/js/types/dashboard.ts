export type MetricComparison = {
    label: string;
    range: string;
    current: number;
    previous: number;
};

export type Metric = {
    total: number;
    comparisons: MetricComparison[];
};

export type DashboardMetrics = {
    posts: Metric;
    books_read: Metric;
    books_tbr: Metric;
};
