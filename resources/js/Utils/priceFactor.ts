export const FACTOR_LABELS: Record<string, string> = {
    pz: 'ЗЦ',
    p1: 'Р1',
    p2: 'Р2',
    p3: 'Р3',
    p4: 'РЦ',
}

export const DISPLAY_FACTORS = ['pz', 'p1', 'p2', 'p3', 'p4'] as const
export const AUTO_FACTORS = ['p3', 'p2', 'p1'] as const

export const DEFAULT_P2_FROM = 200000
export const DEFAULT_P1_FROM = 500000

export type FactorRanges = {
    p2_from: number
    p1_from: number
}

export function asFactorList(value: unknown): string[] {
    if (Array.isArray(value) && value.length) {
        return value.filter((factor): factor is string => typeof factor === 'string' && factor !== '')
    }

    if (typeof value === 'string' && value) {
        try {
            const parsed = JSON.parse(value)
            if (Array.isArray(parsed) && parsed.length) {
                return asFactorList(parsed)
            }
        } catch {
            // scalar string like "p3"
        }
        return [value]
    }

    return ['p3']
}

export function normalizeRanges(ranges?: Partial<FactorRanges> | null): FactorRanges {
    let p2 = Number(ranges?.p2_from)
    let p1 = Number(ranges?.p1_from)

    if (!Number.isFinite(p2) || p2 < 0) p2 = DEFAULT_P2_FROM
    if (!Number.isFinite(p1) || p1 <= p2) {
        p1 = p2 >= DEFAULT_P1_FROM ? p2 + 1 : DEFAULT_P1_FROM
        if (p1 <= p2) p1 = p2 + 1
    }

    return { p2_from: p2, p1_from: p1 }
}

export function pickAutoFactor(
    p3Total: number,
    allowed: string[],
    ranges?: Partial<FactorRanges> | null,
): string {
    const normalized = asFactorList(allowed)
    const auto = AUTO_FACTORS.filter(factor => normalized.includes(factor))

    if (auto.length === 0) {
        return normalized[0] ?? 'p3'
    }

    const { p2_from, p1_from } = normalizeRanges(ranges)
    const wanted = p3Total >= p1_from ? 'p1' : p3Total >= p2_from ? 'p2' : 'p3'
    const order: Record<string, string[]> = {
        p1: ['p1', 'p2', 'p3'],
        p2: ['p2', 'p3', 'p1'],
        p3: ['p3', 'p2', 'p1'],
    }

    return order[wanted].find(factor => auto.includes(factor as typeof AUTO_FACTORS[number])) ?? auto[0]
}

export function factorLabel(factor: string): string {
    return FACTOR_LABELS[factor] || factor.toUpperCase()
}
