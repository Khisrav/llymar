export const FACTOR_LABELS: Record<string, string> = {
    pz: 'ЗЦ',
    p1: 'Р1',
    p2: 'Р2',
    p3: 'Р3',
    p4: 'РЦ',
}

export const DISPLAY_FACTORS = ['pz', 'p1', 'p2', 'p3', 'p4'] as const
export const AUTO_FACTORS = ['p3', 'p2', 'p1'] as const

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

export function pickAutoFactor(p3Total: number, allowed: string[]): string {
    const normalized = asFactorList(allowed)
    const auto = AUTO_FACTORS.filter(factor => normalized.includes(factor))

    if (auto.length === 0) {
        return normalized[0] ?? 'p3'
    }

    const wanted = p3Total >= 500000 ? 'p1' : p3Total >= 200000 ? 'p2' : 'p3'
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
