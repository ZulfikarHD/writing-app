<script setup lang="ts">
import * as d3 from 'd3';
import { computed, onMounted, onUnmounted, ref, watch } from 'vue';

interface RelatedEntry {
    id: number;
    name: string;
    type: string;
}

interface Relation {
    id: number;
    relation_type: string;
    label?: string;
    is_bidirectional: boolean;
    target?: RelatedEntry;
    source?: RelatedEntry;
}

interface Entry {
    id: number;
    name: string;
    type: string;
}

const props = defineProps<{
    entry: Entry;
    outgoingRelations: Relation[];
    incomingRelations: Relation[];
}>();

const emit = defineEmits<{
    (e: 'selectEntry', entryId: number): void;
}>();

const svgRef = ref<SVGSVGElement | null>(null);
const containerRef = ref<HTMLDivElement | null>(null);
const dimensions = ref({ width: 400, height: 300 });

// Type colors
const typeColors: Record<string, string> = {
    character: '#a855f7',
    location: '#3b82f6',
    item: '#f59e0b',
    lore: '#10b981',
    organization: '#f43f5e',
    subplot: '#06b6d4',
};

const getTypeColor = (type: string) => typeColors[type] || '#71717a';

// Build nodes and links from relations
const graphData = computed(() => {
    const nodesMap = new Map<number, { id: number; name: string; type: string; isCenter: boolean }>();
    const links: { source: number; target: number; label: string; is_bidirectional: boolean }[] = [];

    // Add center node (current entry)
    nodesMap.set(props.entry.id, {
        id: props.entry.id,
        name: props.entry.name,
        type: props.entry.type,
        isCenter: true,
    });

    // Add outgoing relations
    props.outgoingRelations.forEach((rel) => {
        if (rel.target) {
            if (!nodesMap.has(rel.target.id)) {
                nodesMap.set(rel.target.id, {
                    id: rel.target.id,
                    name: rel.target.name,
                    type: rel.target.type,
                    isCenter: false,
                });
            }
            links.push({
                source: props.entry.id,
                target: rel.target.id,
                label: rel.label || rel.relation_type,
                is_bidirectional: rel.is_bidirectional,
            });
        }
    });

    // Add incoming relations (only if not bidirectional, to avoid duplicates)
    props.incomingRelations.forEach((rel) => {
        if (rel.source && !rel.is_bidirectional) {
            if (!nodesMap.has(rel.source.id)) {
                nodesMap.set(rel.source.id, {
                    id: rel.source.id,
                    name: rel.source.name,
                    type: rel.source.type,
                    isCenter: false,
                });
            }
            links.push({
                source: rel.source.id,
                target: props.entry.id,
                label: rel.label || rel.relation_type,
                is_bidirectional: false,
            });
        }
    });

    return {
        nodes: Array.from(nodesMap.values()),
        links,
    };
});

const hasRelations = computed(() => graphData.value.nodes.length > 1);

let simulation: d3.Simulation<d3.SimulationNodeDatum, undefined> | null = null;

const updateDimensions = () => {
    if (containerRef.value) {
        const rect = containerRef.value.getBoundingClientRect();
        dimensions.value = {
            width: Math.max(rect.width, 200),
            height: Math.max(rect.height, 200),
        };
    }
};

const renderGraph = () => {
    if (!svgRef.value || !hasRelations.value) return;

    const svg = d3.select(svgRef.value);
    svg.selectAll('*').remove();

    const { width, height } = dimensions.value;
    const { nodes, links } = graphData.value;

    // Create copies for simulation
    const simNodes = nodes.map((n) => ({ ...n }));
    const simLinks = links.map((l) => ({ ...l }));

    // Create arrow marker for directed edges
    svg
        .append('defs')
        .append('marker')
        .attr('id', 'arrowhead')
        .attr('viewBox', '-0 -5 10 10')
        .attr('refX', 20)
        .attr('refY', 0)
        .attr('orient', 'auto')
        .attr('markerWidth', 6)
        .attr('markerHeight', 6)
        .append('path')
        .attr('d', 'M 0,-5 L 10 ,0 L 0,5')
        .attr('fill', '#a1a1aa');

    // Create simulation
    simulation = d3
        .forceSimulation(simNodes as d3.SimulationNodeDatum[])
        .force(
            'link',
            d3
                .forceLink(simLinks)
                .id((d: d3.SimulationNodeDatum) => (d as typeof simNodes[0]).id)
                .distance(100)
        )
        .force('charge', d3.forceManyBody().strength(-300))
        .force('center', d3.forceCenter(width / 2, height / 2))
        .force('collision', d3.forceCollide().radius(40));

    // Create container group
    const g = svg.append('g');

    // Create zoom behavior
    const zoom = d3
        .zoom<SVGSVGElement, unknown>()
        .scaleExtent([0.5, 2])
        .on('zoom', (event) => {
            g.attr('transform', event.transform);
        });

    svg.call(zoom);

    // Create links
    const link = g
        .append('g')
        .attr('class', 'links')
        .selectAll('line')
        .data(simLinks)
        .join('line')
        .attr('stroke', '#a1a1aa')
        .attr('stroke-opacity', 0.6)
        .attr('stroke-width', 1.5)
        .attr('marker-end', (d) => (d.is_bidirectional ? '' : 'url(#arrowhead)'));

    // Create link labels
    const linkLabel = g
        .append('g')
        .attr('class', 'link-labels')
        .selectAll('text')
        .data(simLinks)
        .join('text')
        .attr('font-size', 10)
        .attr('fill', '#71717a')
        .attr('text-anchor', 'middle')
        .attr('dy', -5)
        .text((d) => d.label);

    // Create nodes
    const node = g
        .append('g')
        .attr('class', 'nodes')
        .selectAll('g')
        .data(simNodes)
        .join('g')
        .attr('cursor', 'pointer')
        .on('click', (_event, d) => {
            if (!d.isCenter) {
                emit('selectEntry', d.id);
            }
        })
        .call(
            d3
                .drag<SVGGElement, typeof simNodes[0]>()
                .on('start', (event, d) => {
                    if (!event.active) simulation?.alphaTarget(0.3).restart();
                    d.fx = d.x;
                    d.fy = d.y;
                })
                .on('drag', (event, d) => {
                    d.fx = event.x;
                    d.fy = event.y;
                })
                .on('end', (event, d) => {
                    if (!event.active) simulation?.alphaTarget(0);
                    d.fx = null;
                    d.fy = null;
                }) as d3.DragBehavior<SVGGElement, typeof simNodes[0], typeof simNodes[0] | d3.SubjectPosition>
        );

    // Node circles
    node
        .append('circle')
        .attr('r', (d) => (d.isCenter ? 20 : 15))
        .attr('fill', (d) => getTypeColor(d.type))
        .attr('stroke', '#fff')
        .attr('stroke-width', 2);

    // Node labels
    node
        .append('text')
        .attr('dy', (d) => (d.isCenter ? 35 : 28))
        .attr('text-anchor', 'middle')
        .attr('font-size', (d) => (d.isCenter ? 12 : 10))
        .attr('font-weight', (d) => (d.isCenter ? 'bold' : 'normal'))
        .attr('fill', 'currentColor')
        .text((d) => (d.name.length > 15 ? d.name.substring(0, 12) + '...' : d.name));

    // Update positions on tick
    simulation.on('tick', () => {
        link
            .attr('x1', (d) => (d.source as typeof simNodes[0]).x || 0)
            .attr('y1', (d) => (d.source as typeof simNodes[0]).y || 0)
            .attr('x2', (d) => (d.target as typeof simNodes[0]).x || 0)
            .attr('y2', (d) => (d.target as typeof simNodes[0]).y || 0);

        linkLabel
            .attr('x', (d) => ((d.source as typeof simNodes[0]).x! + (d.target as typeof simNodes[0]).x!) / 2)
            .attr('y', (d) => ((d.source as typeof simNodes[0]).y! + (d.target as typeof simNodes[0]).y!) / 2);

        node.attr('transform', (d) => `translate(${d.x || 0},${d.y || 0})`);
    });
};

// Watch for data changes
watch(
    () => [props.outgoingRelations, props.incomingRelations, dimensions.value],
    () => {
        if (hasRelations.value) {
            renderGraph();
        }
    },
    { deep: true }
);

// Resize observer
let resizeObserver: ResizeObserver | null = null;

onMounted(() => {
    updateDimensions();
    
    if (containerRef.value) {
        resizeObserver = new ResizeObserver(() => {
            updateDimensions();
            if (hasRelations.value) {
                renderGraph();
            }
        });
        resizeObserver.observe(containerRef.value);
    }

    if (hasRelations.value) {
        renderGraph();
    }
});

onUnmounted(() => {
    simulation?.stop();
    resizeObserver?.disconnect();
});
</script>

<template>
    <div ref="containerRef" class="relative h-64 w-full overflow-hidden rounded-lg border border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-800/50">
        <!-- Graph SVG -->
        <svg
            v-if="hasRelations"
            ref="svgRef"
            :width="dimensions.width"
            :height="dimensions.height"
            class="text-zinc-900 dark:text-white"
        />

        <!-- Empty State -->
        <div
            v-else
            class="flex h-full flex-col items-center justify-center text-center"
        >
            <svg class="mb-2 h-8 w-8 text-zinc-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 21L3 16.5m0 0L7.5 12M3 16.5h13.5m0-13.5L21 7.5m0 0L16.5 12M21 7.5H7.5" />
            </svg>
            <p class="text-sm text-zinc-500 dark:text-zinc-400">No relationships</p>
            <p class="text-xs text-zinc-400 dark:text-zinc-500">Add relations to see the graph</p>
        </div>

        <!-- Legend -->
        <div
            v-if="hasRelations"
            class="absolute right-2 bottom-2 flex flex-wrap gap-2 rounded bg-white/80 px-2 py-1 text-xs backdrop-blur dark:bg-zinc-900/80"
        >
            <div v-for="(color, type) in typeColors" :key="type" class="flex items-center gap-1">
                <span
                    class="h-2 w-2 rounded-full"
                    :style="{ backgroundColor: color }"
                />
                <span class="capitalize text-zinc-600 dark:text-zinc-400">{{ type }}</span>
            </div>
        </div>

        <!-- Controls hint -->
        <div
            v-if="hasRelations"
            class="absolute left-2 bottom-2 rounded bg-white/80 px-2 py-1 text-xs text-zinc-500 backdrop-blur dark:bg-zinc-900/80 dark:text-zinc-400"
        >
            Drag nodes • Scroll to zoom • Click to view
        </div>
    </div>
</template>
