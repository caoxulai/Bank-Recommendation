def create_bigram(a):
    ret = []
    for i in range(len(a)-1):
        ret.append(a[i]+" " +a[i+1])
    return reop