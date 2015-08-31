function result = Fmeasure(gnd,clusterinfo)
% Fmeasure - calculate Fmeasure

ctype = unique(clusterinfo);
gtype = unique(gnd);
nc = length(ctype);
ng = length(gtype);
result = 0;

for j = 1:ng
    F = zeros(1,nc);
    gidx = find(gnd == gtype(j));
    mj = length(gidx);
    for i = 1: nc    
        cidx = find(clusterinfo == ctype(i));
        mij = length(intersect(cidx,gidx));
        ni = length(cidx);
        pre_ij = mij / ni;
        rec_ij = mij / mj;
        F(1,i) = 2 * pre_ij * rec_ij / (pre_ij + rec_ij);
    end
    result = result + mj*max(max(F));
end

result = result / length(gnd);

end